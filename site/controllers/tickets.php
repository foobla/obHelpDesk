<?php

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Tickets controller class.
 * @since       1.6
 */
class obHelpDeskControllerTickets extends JControllerForm
{
	function entercode() {
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$session->set('obhelpdesk_data', $data);
		if(obHelpDeskUserHelper::CheckPermissionViewListTicket($user->id)) {
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=tickets', true));
			return true;
		}
		
		if(!self::checkRequiredEnterCode($data)) {
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=tickets&layout=entercode', false));
			return false;
		}
		
		$password = md5($data['password']);
		$query = "SELECT COUNT(*) FROM `#__obhelpdesk3_customers` WHERE `email` = '".trim($data['email'])."' AND `password` = '".$password."'";
		$db->setQuery($query);
		$res = $db->loadResult();
		if($db->getErrorNum()){
			echo '<pre>'.print_r($db->getErrorMsg(), true).'</pre>';
		}
		
		if($res) {
			// IF true --> set session and redirect list ticket page
			$session->set('obhelpdesk_logged', true);
			$session->set('obhelpdesk_email', trim($data['email']));
			$this->setRedirect('index.php?option=com_obhelpdesk&view=tickets');
		} else {
			$session->set('obhelpdesk_logged', false);
			$this->setMessage(JText::_('COM_OBHELPDESK_ENTER_VALID_EMAIL_AND_CODE'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=tickets&layout=entercode', false));
		}
	}
	
	function checkRequiredEnterCode($data) {
		$return = false;
		$email = trim($data['email']);
		$password = $data['password'];
		if(!$email) {
			$msg_error 	= JText::_('Email is required field');
			$this->setMessage($msg_error, 'error');
		}
			
		if(!obHelpDeskHelper::is_email($email)) {
			$msg_error 	= JText::_('Please enter valid email!');
			$this->setMessage($msg_error, 'error');
		}
		
		if(!$password) {
			$msg_error 	= JText::_('Code is required field');
			$this->setMessage($msg_error, 'error');
		}
		
		if(strlen($password) < 8) {
			$msg_error 	= JText::_('Code must be at least 8 characters');
			$this->setMessage($msg_error, 'error');
		}
		
		$return = true;
		
		return $return;
	}
	
	function changecode() {
		$session = JFactory::getSession();
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$data = JRequest::getVar('jform', array(), 'request', 'array');
		$session->set('obhelpdesk_data', $data);
		
		if(!obHelpDeskUserHelper::CheckPermissionViewListTicket($user->id)) {
			$msg = JText::_('NO_PERMISSION');
			$this->setMessage($msg, 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=tickets&layout=entercode', true));
			return true;
		}
		
		if(!self::checkRequiredChangeCode($data)) {
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=tickets&layout=changecode', false));
			return false;
		}
		
		$password = md5($data['password']);
		$query = "SELECT COUNT(*) FROM `#__obhelpdesk3_customers` WHERE `email` = '".trim($data['email'])."' AND `password` = '".$password."'";
		$db->setQuery($query);
		if($db->loadResult()) {
			// IF true --> update new code to database
			$password1 = md5($data['new_password']);
			$query = "UPDATE `#__obhelpdesk3_customers` SET `password` = '".$password1."' WHERE `email` = '".trim($data['email'])."'";
			$db->setQuery($query);
			if($db->query()){
				$this->setMessage(JText::_('Change code successfull!'), 'message');
				$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=tickets', true));
				return true;
			}
		} else {
			$session->set('obhelpdesk_logged', false);
			$this->setMessage(JText::_('COM_OBHELPDESK_ENTER_VALID_EMAIL_AND_CODE'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=tickets&layout=entercode', false));
			return false;
		}
		return true;
	}
	
	function checkRequiredChangeCode($data) {
		$return = false;
		$email = trim($data['email']);
		$password = $data['password'];
		$password1 = $data['new_password'];
		$password2 = $data['new_password2'];
		if(!$email) {
			$msg_error 	= JText::_('COM_OBHELPDESK_ENTER_EMAIL');
			$this->setMessage($msg_error, 'error');
		}
			
		if(!obHelpDeskHelper::is_email($email)) {
			$msg_error 	= JText::_('COM_OBHELPDESK_ENTER_VALID_EMAIL');
			$this->setMessage($msg_error, 'error');
		}
		
		if(!$password) {
			$msg_error 	= JText::_('COM_OBHELPDESK_ENTER_CODE');
			$this->setMessage($msg_error, 'error');
		}
		
		if(strlen($password) < 8) {
			$msg_error 	= JText::_('COM_OBHELPDESK_TICKET_CODE_8CHARACTERS');
			$this->setMessage($msg_error, 'error');
		}
		
		if(!$password1) {
			$msg_error 	= JText::_('New Code is required field');
			$this->setMessage($msg_error, 'error');
		}
		
		if(strlen($password1) < 8) {
			$msg_error 	= JText::_('New Code must be at least 8 characters');
			$this->setMessage($msg_error, 'error');
		}
		
		if(!$password2) {
			$msg_error 	= JText::_('Verify Code is required field');
			$this->setMessage($msg_error, 'error');
		}
		
		if(strlen($password2) < 8) {
			$msg_error 	= JText::_('Verify Code must be at least 8 characters');
			$this->setMessage($msg_error, 'error');
		}
		$return = true;
		
		if($password1 != $password2) {
			$msg_error 	= JText::_('Not match Code');
			$this->setMessage($msg_error, 'error');
		}
		
		return $return;
	}
	
	function bulkupdate() {
		$user = JFactory::getUser();
		
		$is_staff = obHelpDeskUserHelper::is_staff($user->id);
		
		$cid 		= JRequest::getVar('cid', array(), 'request', 'array');
		$priority 	= JRequest::getVar('bulk_priority');
		$status 	= JRequest::getVar('bulk_status');
		$sid 		= JRequest::getVar('staff_id', '_none');
		
		// START CHECK PERMISSION
		if(!$user->id or !$is_staff) {
			$this->setMessage(JText::_('Access Denied'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=ticket&layout=error', false));
			return false;
		}
		
		//==> User must be a Staff.
		if(!obHelpDeskUserHelper::checkPermission($user->id, 'update_ticket')) {
			$this->setMessage(JText::_('Access Denied'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=ticket&layout=error', false));
			return false;
		}
		
		if($status && !obHelpDeskUserHelper::checkPermission($user->id, 'change_ticket_status')) {
			$this->setMessage(JText::_('Access Denied'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=ticket&layout=error', false));
			return false;
		}
		
		if(((int)$sid > 0 ) && !obHelpDeskUserHelper::checkPermission($user->id, 'assign_tickets')) {
			$this->setMessage(JText::_('Access Denied'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=ticket&layout=error', false));
			return false;
		}
		
		// END CHECK PERMISSION.
		
		// If not select anything then show error message and redirect to list tickets page.
		if(!count($cid) or (!$priority and !$status and $sid == '_none')) {
			$this->setMessage(JText::_('Please select ticket(s) and operator'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=tickets', false));
			return false;
		}
		
		// else ==> process update data 
		$db = JFactory::getDbo();
		
		$query = "UPDATE `#__obhelpdesk3_tickets` SET ";
		$set = array();
		if($priority) $set[] = "`priority` = ".$priority;
		if($status) $set[] = "`status`='".$status."'";
		if($sid != '_none') $set[] = "`staff`='".$sid."'";
		$query .= implode(', ', $set);
		$query .= " WHERE `id` IN (".implode(',', $cid).")"; 
		$db->setQuery($query);
		
		if (!$db->query()) {
			JError::raiseError(500, $db->getErrorMsg());
		}
		$this->setMessage(JText::_('Updated Successfull!'), 'message');
		$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=tickets', true));
		return true;
	}
	
	function bulkdelete() {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$is_staff = obHelpDeskUserHelper::is_staff($user->id);
		
		// START CHECK PERMISSION
		if(!$user->id or !$is_staff) {
			$this->setMessage(JText::_('Access Denied'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=ticket&layout=error', false));
			return false;
		}
		
		//==> User must be a Staff.
		if(!obHelpDeskUserHelper::checkPermission($user->id, 'delete_ticket')) {
			$this->setMessage(JText::_('Access Denied'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=ticket&layout=error', false));
			return false;
		}
		
		$cid = JRequest::getVar('cid', array(), 'request', 'array');
		// If not select anything then show error message and redirect to list tickets page.
		if(!count($cid)) {
			$this->setMessage(JText::_('Please select ticket(s)'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=tickets', false));
			return false;
		}
		
		
		// else ==> process update data 
		$db = JFactory::getDbo();
		$cid_str = implode(',', $cid);
		// delete message
		$query = "DELETE FROM `#__obhelpdesk3_messages`
					WHERE `tid` IN({$cid_str})";
		$db->setQuery($query);
		$db->query();
		if($db->getErrorNum()) {
			$app->enqueueMessage('COM_OBHELPDESK_ERROR_ON_REMOVE_TICKET_MESSAGES');
			$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=tickets', true));
		}
		// delete ticket
		$query = "DELETE FROM `#__obhelpdesk3_tickets` WHERE `id` IN (".implode(',', $cid).")"; 
		$db->setQuery($query);
		
		if (!$db->query()) {
			JError::raiseError(500, $db->getErrorMsg());
		}
		$this->setMessage(JText::_('COM_OBHELPDESK_TICKETS_REMOVED'), 'message');
		$this->setRedirect(JRoute::_('index.php?option=com_obhelpdesk&view=tickets', true));
		return true;
	}
}
