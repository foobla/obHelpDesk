<?php

defined('_JEXEC') or die;

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
/**
 * Methods supporting a list of department records.
 */
class obHelpDeskModelTicket extends JModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 */
	public function getTable($type = 'Ticket', $prefix = 'obHelpDeskTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem($pk = null) 
	{
		$session = JFactory::getSession();
		$tid = ($session->get('obhelpdesk_tid')) ? $session->get('obhelpdesk_tid') : 0;
		return parent::getItem($tid);
	}
	/**
	 * Method to get the record form.
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_obhelpdesk.ticket', 'ticket', array('control' => 'jform', 'load_data' => $loadData));
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
		$session = JFactory::getSession();
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_obhelpdesk.edit.reply.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}
	
	function getMessages($tid) {
		if((int)$tid == 0 ) return false;
		
		$db = JFactory::getDbo();
		$query = "SELECT m.*, u.email as umail, u.name as uname, oc.email as cmail, oc.fullname as cname"
				." FROM `#__obhelpdesk3_messages` as m"
				." LEFT JOIN `#__users` as u ON m.`user_id` = u.`id`"
				." LEFT JOIN `#__obhelpdesk3_customers` as oc ON m.`email` = oc.`email`"
				." WHERE m.`tid`=".$tid." ORDER BY `id` DESC
						LIMIT 5";
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getDefaultReplyTemplate(){
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$sql = "SELECT 
					    `content`
					FROM
					    #__obhelpdesk3_replytemplates
					WHERE
					    staff_id = {$user->id} AND `default` = 1";
		$db->setQuery($sql);
		$content = $db->loadResult();
		return $content;
	}
	
	function getDepartmentList(){
		$item = $this->getItem();
// 		echo '<pre>'.print_r( $item, true ).'</pre>';
		$value = $item->departmentid;
		$app 	= JFactory::getApplication();
		$user 	= JFactory::getUser();
		$dids = array();
		$is_staff = obHelpDeskUserHelper::is_staff($user->id) ;
		if($user->id && $is_staff) {
				$dids = obHelpDeskUserHelper::getStaffDepartment($user->id);
		}else {
			$department = obHelpDeskTicketHelper::getDepartment($item->departmentid);
			return $department->title;
		}

		$db = JFactory::getDbo();
		if(count($dids)) {
			$query = "SELECT `title` as text, `id` as value FROM `#__obhelpdesk3_departments` WHERE `id` IN (".implode(',', $dids).") AND `published`=1";
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			$arr_obj = array();
			if(count($rows)){
				foreach ($rows as $row){
					$obj = new stdClass();
					$obj->text = $row->text;
					$obj->value = $row->value;
					array_push($arr_obj, $obj);
				}
			}
			$javascript = ' onchange="loadfields(this.form);"';
			return JHTML::_('select.genericlist',  $arr_obj, 'jform[departmentid]', 'class="inputbox span3"'.$javascript, 'value', 'text', $value);
		} else {
			return false;
		}
	}
}
