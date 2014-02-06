<?php
/**
* @package		$Id: department.php 48 2013-09-03 04:08:14Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * Department Model
 */
class obHelpDeskModelDepartment extends JModelAdmin
{
	public $array_type = array(
		'text' => 'Text',
		'list' => 'Select',
		'radio' => 'Radio',
		'checkbox' => 'Checkbox',
		'checkboxes' => 'Checkbox list',
		'calendar' => 'Calendar',
		'textarea' => 'Textarea',
		'datetime' => 'Datetime',
	);
	
	/**
	 * Returns a reference to the a Table object, always creating it.
	 */
	public function getTable($type = 'Department', $prefix = 'obHelpDeskTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	 * Method to get the record form.
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_obhelpdesk.department', 'department', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	/**
	 * Method get field type
	 */
	public function getOptions()
	{
		// Initialise variables.
		$arr_options = $this->array_type;
		foreach ($arr_options as $key=>$option) {
			$arr_options[$key] = JText::_($option);
		}
		return $this->array_type;	
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
		$data = JFactory::getApplication()->getUserState('com_obhelpdesk.edit.field.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}
		$data->kb_catid = explode(',', $data->kb_catid);
		return $data;
	}

	public function save($data) {
		$jform = JRequest::getVar('jform');
		
		// save user's groups
		$groups = $jform['usergroups'];
		if($groups) $usergroups = implode(',', $groups);
		else $usergroups = 1;
		$data['usergroups'] = $usergroups;
		
		// save custom fields.
		$fields = '';
		if($jform['fields']) $fields = implode(',', $jform['fields']);
		$data['fields'] = $fields;
		$data['kb_catid']= implode(',', $jform['kb_catid']);
		return parent::save($data);
	}
	
}
