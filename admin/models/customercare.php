<?php
/**
* @package		$Id: customercare.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * Customercare Model class
 */
class obHelpDeskModelCustomercare extends JModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 */
	public function getTable($type = 'Customercare', $prefix = 'obHelpDeskTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	 * Method to get the record form.
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_obhelpdesk.customercare', 'customercare', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	/**
	 * Method getItem()
	 */
	public function getItem($pk = null) 
	{
		$item = parent::getItem($pk);
		if($item->id) {
			if($item->staff_id_list) {
				$staffs = explode(',', $item->staff_id_list);
				$staff_id_list = array();
				foreach ($staffs as $staff_id) {
					$obj = new stdClass();
					$obj->value = $staff_id;
					$staff_id_list[] = $obj;
				}
				$item->staff_id_list = $staff_id_list;
			}
		}
		return $item;
	}
	
	public function save($data) {
		$staffs = $data['staff_id_list'];
		if($staffs) $data['staff_id_list'] = implode(',', $staffs);
		return parent::save($data);
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
		$data = JFactory::getApplication()->getUserState('com_obhelpdesk.edit.customercare.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}
}
