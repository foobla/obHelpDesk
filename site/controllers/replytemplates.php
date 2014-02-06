<?php

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

/**
 * Canned Responses controller class.
 * @since       1.6
 */
class obHelpDeskControllerReplyTemplates extends JControllerForm
{
	
	function __construct( $default = array())
	{
		parent::__construct( $default );
		// here is where register tasks 
	}
	
	function set_default() {
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		
		$cid = JRequest::getVar('cid');
		$id = (int) $cid[0];
		if(!$id) {
			$msg = JText::_('COM_OBHELPDESK_MSG_UPDATE_DEFAULT_CANNED_FAILED');
			$this->setMessage($msg, 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=replytemplates', false));
			return false;
		}
		$query = "UPDATE `#__obhelpdesk3_replytemplates` SET `default`=0 WHERE `staff_id`=".$user->id;
		$db->setQuery($query);
		$db->query();
		
		$time = JFactory::getDate();
		$query = "SELECT * FROM `#__obhelpdesk3_replytemplates` WHERE `id`=".$id;
		$db->setQuery($query);
		$row = $db->loadObject();
		if($row->staff_id == $user->id) {
			$query = "UPDATE `#__obhelpdesk3_replytemplates` SET `default`=1, `published` = 1, `enable` = 1 WHERE `id`=".$id;
			$db->setQuery($query);
			$db->query();
		} elseif(!$row->copy_from) {
				$query = "INSERT INTO `#__obhelpdesk3_replytemplates`"
								." SET `subject`='".addslashes($row->subject)."',"
								." `content`='".addslashes($row->content)."',"
								." `default` = 1,"
								." `enable` = 1,"
								." `published` = 1,"
								." `copy_from`=".$id.","
								." `modified_date`='".$time->toSql()."',"
								." `created_date`='".$time->toSql()."',"
								." `staff_id` = ".$user->id;
				$db->setQuery($query);
				$db->query();
		} 
		
		$msg = JText::_('COM_OBHELPDESK_MSG_UPDATE_DEFAULT_CANNED_SUCCESS');
		$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=replytemplates'), $msg, true);
		return true;
	}
	
	function enable() {
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		
		$cid = JRequest::getVar('cid');
		if(!count($cid)) {
			$msg = JText::_('COM_OBHELPDESK_MSG_ENABLE_TEMPLATE_FAILED');
			$this->setMessage($msg, 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=replytemplates', false));
			return false;
		}
		foreach($cid as $id) {
			$query = "SELECT * FROM `#__obhelpdesk3_replytemplates` WHERE `id`=".$id;
			$db->setQuery($query);
			$row = $db->loadObject();
			$time = JFactory::getDate();
			if($row->staff_id == $user->id) {
				$query = "UPDATE `#__obhelpdesk3_replytemplates` SET `enable`=1, `published` = 1 WHERE `id`=".$id;
			} elseif(!$row->enable && !$row->copy_from) {
				$query = "INSERT INTO `#__obhelpdesk3_replytemplates`"
								." SET `subject`='".addslashes($row->subject)."',"
								." `content`='".addslashes($row->content)."',"
								." `enable` = 1,"
								." `published` = 1,"
								." `copy_from`=".$id.","
								." `modified_date`='".$time->toSql()."',"
								." `created_date`='".$time->toSql()."',"
								." `staff_id` = ".$user->id;
			}
			$db->setQuery($query);
			$db->query();
		}
		$msg = JText::_('CON_OBHELPDESK_ENABLE_TEMPLATE_SUCCESSFUL');
		$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=replytemplates'), $msg);
		return true;
	}
	
	function disable() {
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		
		$cid = JRequest::getVar('cid');
		$count = count($cid);
		if(!$count) {
			$msg = JText::_('COM_OBHELPDESK_MSG_SELECT_TEMPLATE');
			$this->setMessage($msg, 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=replytemplates', false));
			return false;
		}
		foreach($cid as $id) {
			$query = "SELECT * FROM `#__obhelpdesk3_replytemplates` WHERE `id`=".$id;
			$db->setQuery($query);
			$row = $db->loadObject();
			if($row->staff_id == $user->id) {
				$query = "UPDATE `#__obhelpdesk3_replytemplates` SET `enable`=0 WHERE `id`=".$id;
				$db->setQuery($query);
				$db->query();
			} else {
				$count --;
			}
		}
		if($count == 0) {
			$msg = JText::_('COM_OBHELPDESK_MSG_DENIED');
			$this->setMessage($msg, 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=replytemplates', false));
			return false;
		}
		$msg = JText::_('COM_OBHELPDESK_MSG_DISABLE_TEMPLATE_FAILED');
		$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=replytemplates'), $msg);
		return true;
	}
	
	function remove(){
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		
		$cid = JRequest::getVar('cid', 0);
		
		if(!$cid) {
			$msg = JText::_('COM_OBHELPDESK_MSG_MAKE_SELECT_FIRST');
			$this->setMessage($msg, 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=replytemplates', false));
			return false;
		}
		$cid_tmp = array();
		foreach($cid as $id) {
			$query = "SELECT * FROM `#__obhelpdesk3_replytemplates` WHERE `id`=".$id;
			$db->setQuery($query);
			$row = $db->loadObject();
			if($row->staff_id == $user->id) {
				$cid_tmp[] = $id;
			}
		}
		$cid_imp = implode(',', $cid_tmp);
		$query = "DELETE FROM `#__obhelpdesk3_replytemplates` WHERE `id` IN (".$cid_imp.")";
		$db->setQuery($query);
		if(!$db->query()) {
			$msg = JText::_('COM_OBHELPDESK_MSG_CANNOT_DELETE_CANNED_RESPONSE');
			$this->setMessage($msg, 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=replytemplates', false));
			return false;
		} 
		
		$msg = JText::_('COM_OBHELPDESK_MSG_SUCCESS');
		$this->setMessage($msg, 'message');
		$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=replytemplates', true));
		return true;
	}
	
	public function getreplytemplate(  ) {
		$template_id	= JRequest::getVar('template_id');
		$customer_id 	= JRequest::getVar('customer_id');
		$customer_email = JRequest::getVar('customer_email');
		if( !$template_id || !$customer_id || !$customer_email ){
			jexit();
		}
		$db 	= JFactory::getDbo();
		$sql 	= "SELECT 
						`content` 
					FROM 
						`#__obhelpdesk3_replytemplates` 
					WHERE 
						`id`={$template_id};";
		$db->setQuery($sql);
		$content = $db->loadResult();

		if( !$content ) {
			jexit();
		}

		$content = obHelpDeskTicketHelper::getReplyTemplate($content, $customer_id, $customer_email);
		$content = base64_encode(obHelpDeskHelper::bbcodetoHTML(obHelpDeskHelper::html2bbcode($content)));;

		echo $content;

		/* Update hits */
		$query = '
			UPDATE `#__obhelpdesk3_replytemplates`
			SET
				`hits` = `hits` + 1
			WHERE
				`id` = '.$template_id.'
		';

		$db->setQuery($query);
		if(!$db->query($query)) {
			echo $db->getErrorMsg();
		}

		jexit();
	}
}