<?php
/**
* @package		$Id: replytemplate.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * Replytemplate Model
 */
class obHelpDeskModelReplytemplate extends JModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 */
	public function getTable($type = 'Replytemplate', $prefix = 'obHelpDeskTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	 * Method to get the record form.
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_obhelpdesk.replytemplate', 'replytemplate', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_obhelpdesk.edit.replytemplate.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}
	
	/**
	 * 
	 * List all Canned Response keyword ...
	 */
	public function getTemplateKey()
	{
		$templateKey = array();
		$templateKey["{username}"] = 'Show staff name';
		$templateKey["{customer}"] = 'Show customer name';
		$templateKey["{date}"] = 'Show current date';
		$templateKey["{cursor}"] = 'Set cursor to here when start to reply';
	
		return $templateKey;
	}
	
	public function save($data)
	{
// 		$date = JFactory::getDate()->toFormat('%Y-%m-%d %H:%M:%S');
		$date = JFactory::getDate()->toSql();
		$data['modified_date'] = $date; 
		if($data['created_date'] == '') $data['created_date'] = $date;
		
		return parent::save($data);
	}
}
