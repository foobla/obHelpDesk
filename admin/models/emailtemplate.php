<?php
/**
* @package		$Id: emailtemplate.php 68 2013-09-11 11:19:04Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * Emailtemplate Model
 */
class obHelpDeskModelEmailtemplate extends JModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 */
	public function getTable($type = 'Emailtemplate', $prefix = 'obHelpDeskTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	 * Method to get the record form.
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_obhelpdesk.emailtemplate', 'emailtemplate', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_obhelpdesk.edit.emailtemplate.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}
	
	/**
	 * 
	 * List all email template keyword ...
	 */
	public function getTemplateKey()
	{
		$templateKey = array();

		#SITE
		$templateKey['{site_name}']				= JText::_('COM_OBHELPDESK_SITE_NAME');
		$templateKey['{site_url}'] 				= JText::_('COM_OBHELPDESK_SITE_URL');

		#TICKET
		$templateKey['{ticket_code}'] 			= JText::_('COM_OBHELPDESK_SUBJECT_OF_TICKET');
		$templateKey['{ticket_subject}'] 		= JText::_('COM_OBHELPDESK_SUBJECT_OF_TICKET');
		$templateKey['{ticket_url}']			= JText::_('COM_OBHELPDESK_TICKET_URL');
		$templateKey['{staff_name}']			= JText::_('COM_OBHELPDESK_TICKET_URL');
		$templateKey['{staff_email}']			= JText::_('COM_OBHELPDESK_TICKET_URL');
		$templateKey['{customer_name}'] 		= JText::_('COM_OBHELPDESK_SHOW_CUSTOMER_NAME');
		$templateKey['{customer_email}'] 		= JText::_('COM_OBHELPDESK_SHOW_CUSTOMER_NAME');
		$templateKey['{custom_fields}'] 		= JText::_('COM_OBHELPDESK_SHOW_CUSTOM_FIELD');

		#MESSAGE
		$templateKey['{message_fromname}'] 		= JText::_('COM_OBHELPDESK_SHOW_STAFF_NAME');
		$templateKey['{message_fromemail}'] 	= JText::_('COM_OBHELPDESK_SHOW_STAFF_NAME');
		$templateKey['{message_body}'] 	= JText::_('COM_OBHELPDESK_SHOW_STAFF_NAME');

		#OTHER
		$templateKey['{overdue_time}'] 			= JText::_('COM_OBHELPDESK_SHOW_OVERDUE_TIME');

		return $templateKey;
	}
	
	public function save($data)
	{
		$date = JFactory::getDate()->Format('%Y-%m-%d %H:%M:%S');
		
		$data['modified_date'] = $date; 
		if($data['created_date'] == '') $data['created_date'] = $date;
		
		return parent::save($data);
	}
}
