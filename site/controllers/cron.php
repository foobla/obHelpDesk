<?php

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

/**
 * Ticket controller class.
 * @since       1.6
 */
class obHelpDeskControllerCron extends JControllerForm
{
	/**
	 * View Reply Form
	 */
	function cron_email( ) {
		//connect to mailbox
		$params = JComponentHelper::getParams('com_obhelpdesk');
		$cron_enable = $params->get('cron_enable');
		if(!$cron_enable){
			exit(''.__LINE__);
		}
		$cron_port 			= $params->get('cron_port');
		$cron_protocol 		= $params->get('cron_protocol');
		$cron_ssl 			= $params->get('cron_ssl');
		$cron_username 		= $params->get('cron_username');
		$cron_password 		= $params->get('cron_password');
		$cron_servername 	= $params->get('cron_servername');
		$cron_acceptnewtickets = $params->get('cron_acceptnewtickets');

		$mbox = imap_open("{".$cron_servername.":".
							$cron_port.'/'.
							$cron_protocol.
							$cron_ssl."/novalidate-cert}INBOX", $cron_username, $cron_password);
		
		$imap_errors = imap_errors();
		if ( !$mbox ) exit();
		$totalmsg = imap_num_msg($mbox);
		
		if(!$totalmsg) exit();
		
		$totalmsg =($totalmsg>100)?100:$totalmsg;
		
		
		//parse each message
		for ($i=1;$i<=$totalmsg; $i++) {
			//clear previous message
			unset($msg);
			$msg = array();
		
			//message header
			$header 		= imap_header($mbox, $i);
			$from 			= $header->from[0];
			$from_email 	= $from->mailbox.'@'.$from->host;

			$message_date 	= $header->udate;
			$date 			= JFactory::getDate($message_date);
			$reply_time 	= $date->toSql();
			
			$user 	= obHelpDeskUserHelper::getUserInfoByEmail($from_email);
			if( !$user && $cron_acceptnewtickets!='all' ) {
				continue;
			}
			//get from
			$subject 		= imap_utf8($header->subject);
			preg_match('/\[([A-z0-9\-\_]+)*\-(\d+)\]/', $subject, $matches);
			$ticket_code 		= '';
			$department_code 	= '';
			$ticket_id 			= null;
			
			if( $matches && count($matches)==3 ){
				$ticket_code 		= $matches[0];
				$department_code 	= $matches[1];
				$ticket_id 			= $matches[2];

				// load ticket
				$ticket = obHelpDeskTicketHelper::load($ticket_id);

				// get message body
				$mail 			= new obHelpDeskMail($mbox, $i);
// 				$message_body 	= @$mail->htmlmsg ? @$mail->htmlmsg : @$mail->plainmsg;

				$message_body = $mail->gmailReply();

				$res 		= $this->getAttachFiles($mail->attachments,$reply_time);
				$files 		= (count($res['files']))?implode("\n", $res['files'] ):'';
				$attachment = $res['fullpath'];
				
				$ticket = obHelpDeskTicketHelper::addTicketMessage($ticket_id, $user, $message_body, $reply_time, $files, $attachment );
				if($ticket)
					imap_delete($mbox,$i);
			
			} elseif ( $cron_acceptnewtickets=='users' && $user->id ) {
				$cron_defaultdepartment = $params->get('cron_defaultdepartment');
				$mail 			= new obHelpDeskMail($mbox, $i);
// 				$message_body 	= @$mail->htmlmsg ? @$mail->htmlmsg : @$mail->plainmsg;
				$message_body = $mail->gmailReply();
				$res 		= $this->getAttachFiles($mail->attachments,$reply_time);
				$files 		= (count($res['files']))?implode("\n", $res['files'] ):'';
				$attachment = $res['fullpath'];
				
				$ticket = obHelpDeskTicketHelper::addTicket($user, $cron_defaultdepartment, $subject, $message_body, $reply_time, $files, $attachment);
				if($ticket)
					imap_delete($mbox,$i);
				
			} elseif ( $cron_acceptnewtickets == 'all'){
				# create new user
				if(!$user || !$user->id ){
					$from_name 	= imap_utf8($from->personal);
					$user 		= obHelpDeskUserHelper::createUser($from_name, $from_email);
					if(!$user->id) continue;
				}
				
				# add new ticket
				$cron_defaultdepartment = $params->get('cron_defaultdepartment');
				$mail 			= new obHelpDeskMail($mbox, $i);
// 				$message_body 	= @$mail->htmlmsg ? @$mail->htmlmsg : @$mail->plainmsg;
				$message_body = $mail->gmailReply();
				$res 		= $this->getAttachFiles($mail->attachments,$reply_time);
				$files 		= (count($res['files']))?implode("\n", $res['files'] ):'';
				$attachment = $res['fullpath'];

				$ticket 	= obHelpDeskTicketHelper::addTicket( $user, $cron_defaultdepartment, $subject, $message_body, $reply_time, $files, $attachment);
				if($ticket)
					imap_delete($mbox,$i);
			}
			echo '<hr/>';
		}
		//commit delete
		imap_expunge($mbox);
		//close connection
		imap_close($mbox);
		exit(''.__LINE__);
	}
	
	function auto_close( ) {
		$params = JComponentHelper::getParams('com_obhelpdesk');
		$autoclose_Enable = $params->get('autoclose_Enable');
		if(!$autoclose_Enable){
			exit(''.__LINE__);
		}

		$db = JFactory::getDbo();
		$autoclose_Interval = $params->get('autoclose_Interval');

		$query = '
			SELECT `tid`,MAX(UNIX_TIMESTAMP(`reply_time`))
			FROM `#__obhelpdesk3_messages` AS `m` INNER JOIN #__obhelpdesk3_tickets AS `t`
					ON `m`.`tid` = `t`.`id` AND `t`.`status`="on-hold"
				
			GROUP BY `tid`
				HAVING MAX(UNIX_TIMESTAMP(`reply_time`)) + '.($autoclose_Interval*86400).'<='.time().'
			ORDER BY `tid`';

		$db->setQuery($query);
		$cfs = $db->loadObjectList();

		if( $db->getErrorNum() ){
			print_r($db->getErrorMsg());
		}
		$count = count($cfs);
		if( $count > 0 ) {
			echo '<h1>'.$count.' ticket will be close</h1>';
			$arr_cfs_id = array();
			foreach ($cfs as $cf){
				$arr_cfs_id[] = $cf->tid;
			}
			$str_cfs =  implode(',', $arr_cfs_id);

			//close expired tickets
			$query = '
				UPDATE  `#__obhelpdesk3_tickets`
				SET  `status` =  \'closed\'
				WHERE
					`id` IN ('.$str_cfs.') AND
					`status` = \'on-hold\'
			';
			//var_dump($query);die();
			$db->setQuery($query);
			$db->query();
			if( $db->getErrorNum() ){
				print_r($db->getErrorMsg());
			}
		}
		exit(''.__LINE__);
	}
	
	private function getAttachFiles( $files, $reply_time ){
		jimport('joomla.filesystem.file');
		$arr_files 				= array();
		$attachment				= array();
		$filepath				= JPATH_COMPONENT.DS.'uploads'; // we need a way to config this storage
		$str_time = JFactory::getDate($reply_time)->format('YmdHis');
		
		foreach ( $files as $file ) {
			if( $file ) {
				$arr_files[] = $file['filename'];
				$f 		= $str_time. $file['filename'];
				$attachment[] = $filepath.DS.$f;
				JFile::write($filepath.DS.$f, $file['contents']);
			}
		}
		return array('files'=>$arr_files, 'fullpath'=>$attachment);
	}
}