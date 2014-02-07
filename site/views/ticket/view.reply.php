<?php
/**
* @package		$Id: view.reply.php 102 2013-12-11 02:03:30Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class obHelpDeskViewTicket extends obView
{
	protected $form;
	protected $item;
	
	function display($tpl = null)
	{
		$this->viewTicketWithoutLogin();
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		
		$tid = JRequest::getVar( "id", 0, null, "int");
		if($tid) $session->set('obhelpdesk_tid', $tid);
		
		$is_staff = obHelpDeskUserHelper::is_staff($user->id);
		// Check Permission View ticket
		if(!obHelpDeskUserHelper::checkViewTicketPermission($user->id, $tid, $is_staff)) { 
			// if have not permission
			$msg = JText::_('COM_OBHELPDESK_MSG_NO_PERMISSION_VIEW_TICKET');
			$app->enqueueMessage($msg, 'warning');
			$app->redirect(JRoute::_('index.php?option=com_obhelpdesk&view=error'));
		}
		
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		$this->departmentlist = $this->get('DepartmentList');
		
		if(!$tid) {
			$msg = JText::_('Please select a ticket');
			$app->enqueueMessage($msg, 'notice');
			$app->redirect(JRoute::_('index.php?option=com_obhelpdesk&view=tickets'));
		}
		$perm_update = obHelpDeskUserHelper::checkPermission($user->id, 'update_ticket');
		$perm_answer = obHelpDeskUserHelper::checkPermission($user->id, 'answer_ticket');
// 		var_dump($perm_update);
// 		exit();
		if($user->id) {
			$this->perm_update = ($user->email == $this->item->customer_email or $user->id == $this->item->customer_id) ? true : $perm_update;
			$this->perm_answer = ($user->email == $this->item->customer_email or $user->id == $this->item->customer_id) ? true : $perm_answer;
			
		} else {
// 			$this->perm_update = ($session->get('obhelpdesk_email') == $this->item->customer_email) ? true : false;
// 			$this->perm_answer = $this->perm_update;
			$this->perm_answer = $perm_answer;
			$this->perm_update = $perm_update;
		}
		$this->perm_delete = obHelpDeskUserHelper::checkPermission($user->id, 'delete_ticket');
		
		$this->fields = obHelpDeskTicketHelper::getFields($this->item->departmentid, $this->item->id);
		$model = $this->getModel();
		$messages = $model->getMessages($tid);
		
		$this->listAssignee = false;
		$this->listStatus = false;
		$this->listPriority = false;
		$this->updatePerm = obHelpDeskUserHelper::checkPermission($user->id, 'update_ticket');
		$this->is_staff = $is_staff;
		if($is_staff) {
			
			if(obHelpDeskUserHelper::checkPermission($user->id, 'assign_tickets')) {
				$this->listAssignee = obHelpDeskUserHelper::getStaffList($this->item->staff);
			} else {
				$this->listAssignee = obHelpDeskUserHelper::getStaffList($this->item->staff, true);
			}
			if(obHelpDeskUserHelper::checkPermission($user->id, 'change_ticket_status')) {
				$this->listStatus = obHelpDeskTicketHelper::getListStatus($this->item->status);
			} else {
				$this->listStatus = obHelpDeskTicketHelper::getListStatus($this->item->status, true);
			}
			
			if($this->updatePerm) {
				$this->listPriority = obHelpDeskTicketHelper::getListPriority($this->item->priority);
			} else {
				$this->listPriority = obHelpDeskTicketHelper::getListPriority($this->item->priority, true);
			}
		} else {
			$this->listStatus = obHelpDeskTicketHelper::getListStatus($this->item->status, true);
			$this->listPriority = obHelpDeskTicketHelper::getListPriority($this->item->priority, true);
		}
		
		// require BBCode Editor.
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'editor_bbcode.php';
		$editor_bbcode = new ObEditorBBcode();
		$this->bbcode = $editor_bbcode;
		if($this->item->status != 'closed') {
			$content = '';
			$default_reply_template = $this->get('DefaultReplyTemplate');
			if($default_reply_template){
				$content = obHelpDeskHelper::html2bbcode(obHelpDeskTicketHelper::getReplyTemplate($default_reply_template, $this->item->customer_id, $this->item->customer_email));
			}
			
			$config = array();
			if( !$is_staff ) {
				$config = array('bold','italic','underline','hypelink','image','list','color','quote','source');
			}
			$this->content = $editor_bbcode->display( 'ticket_message', $content, $config );
		}
		$this->department = obHelpDeskTicketHelper::getDepartment($this->item->departmentid);
		$this->item->code = $this->department->prefix.'-'.$this->item->id;
		$attachkey = obHelpDeskHelper::getConfig('utility_attachkey');
		$this->attachkey = $attachkey->value;
		$this->email = ($user->id)? $user->email : $session->get('obhelpdesk_email');
		$this->replytemplate = '';
		
		$this->messages = $messages;
		parent::display($tpl);
	}
	
	public function viewTicketWithoutLogin(){
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		if( !$user->id ) {
			$id 	= JRequest::getVar('id');
			$email 	= addslashes(JRequest::getVar('email'));
			if(!$email){
				$email 	= $app->getUserState('obhelpdesk_ticket_email');
			}
			
			$code 	= addslashes(JRequest::getVar('quickcode'));
			if( !$code ) {
				$code 	= $app->getUserState('obhelpdesk_ticket_code');
			}
			$uri 	= JURI::getInstance();
			$return = base64_encode($uri->toString());
			if( $email && $code ) {
				$db 	= JFactory::getDbo();
				$sql 	= "SELECT
								`id`,
								`customer_id`,
								`customer_email`,
								`quickcode`
							FROM
								`#__obhelpdesk3_tickets`
							WHERE
								`id`={$id} AND
								`customer_email`='{$email}' AND
								`quickcode`='{$code}'";
				$db->setQuery($sql);
				$res = $db->loadAssoc();

				if( $db->getErrorNum() ) {
					$msg = $db->getErrorMsg();
					$app->enqueueMessage($msg, 'error');
				}

				if( !$res ){
					#TODO: redirect to login form of HelpDesk
					$msg 	= JText::_('COM_OBHELPDESK_REQUIRED_LOGIN');
					$app->redirect('index.php?option=com_users&view=login&Itemid=141&return='.$return, $msg, 'error');
				} else {
					#TODO: set permission allow user view ticket
					$app->setUserState('obhelpdesk_logged', true);
					$app->setUserState('obhelpdesk_ticket_email', $email);
					$app->setUserState('obhelpdesk_ticket_code', $code);

/* 
					$obhelpdesk_user = new JUser();
					$obhelpdesk_user->load( $res['customer_id'] );
					$app->setUserState('obhelpdesk_user', $obhelpdesk_user); 
*/

				}
			} else {
				#TODO: redirect to login form
				$msg = JText::_('COM_OBHELPDESK_REQUIRED_LOGIN');
				$app->redirect('index.php?option=com_users&view=login&Itemid=141&return='.$return, $msg, 'error');
			}
		}
	}

} // end class
?>