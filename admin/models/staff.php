<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * Staff Model class
 */
class obHelpDeskModelStaff extends JModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 */
	public function getTable($type = 'Staff', $prefix = 'obHelpDeskTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	 * Method to get the record form.
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_obhelpdesk.staff', 'staff', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_obhelpdesk.edit.staff.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}
	
	/**
	 * Method getItem()
	 */
	public function getItem($pk = null) 
	{
		$item = parent::getItem($pk);
		if($item->id) {
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select(
				$this->getState(
					'list.select',
					'a.*'
				)
			);
	
			$query->from($db->quoteName('#__obhelpdesk3_staff_department').' AS a');
			$query->where('a.user_id = '.(int) $item->user_id);
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			$de = array();
			if(count($rows)) {
				foreach($rows as $row) {
					$obj = new stdClass();
					$obj->value = $row->department_id;
					$de[] = $obj;
				}
				$item->department_id = $de;
			}
		}
		return $item;
	}
	
	/**
	 * Method to save the form data.
	 */
	public function save($data)
	{
		$id = $data['id'];
		$userid = $data['user_id'];
		$dep = $data['department_id'];
		
		if (parent::save($data)) {
			if(count($dep)) {
				// insert data to obhelpdesk_staff_department table
				$db = $this->getDbo();
				$query = $db->getQuery(true);
				
				$query = "DELETE FROM `#__obhelpdesk3_staff_department` WHERE `user_id`=".$userid;
				$db->setQuery($query);
				$db->query();
				
				$arr_values = array();
				foreach ($dep as $d) {
					$values[] = "($userid, $d)";
				}
				
				$query = $db->getQuery(true);
				$query = 'INSERT INTO #__obhelpdesk3_staff_department('.$db->quoteName('user_id').', '.$db->quoteName('department_id').')' .
					' VALUES '.implode(',', $values);
				$db->setQuery($query);
				
				if (!$db->query()) {
					$this->setError($db->getErrorMsg());
					return false;
				}
			}
			return true;
		}
		return false;
	}
}
