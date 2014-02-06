<?php
/**
* @package		$Id: reports.php 23 2013-08-15 10:15:43Z phonglq $ obHelpDesk
* @author 		Kien nguyen - foobla.com.
* @copyright	Copyright (C) 2007-2011 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');
class obHelpDeskModelReports extends obModel
{
//	var $request_parameters;
	var $report_parameters;
	var $data;
	var $data_table;
//	var $header_data;
	var $list_departments;
	var $list_staffs;
	var $list_customers;
	var $calendar_from;
	var $calendar_to;
	var $calendar_unit;
	var $report_type;
	var $selectedDepartment;
	var $selectedStaff;
	var $selectedCustomer;

	function __construct()
	{
	 	parent::__construct();
	 	$this->report_type = JRequest::getVar('report', '');
		$this->calendar_from = JRequest::getVar('calendar_from', date('Y-m-d',mktime(0, 0, 0, date("m"), date("d")-29, date("Y"))));
		$this->calendar_to = JRequest::getVar('calendar_to', date('Y-m-d'));
		$this->calendar_unit = JRequest::getVar('calendar_unit', 'day');
		$this->selectedDepartment= JRequest::getVar('department_id', '');
		$this->selectedStaff= JRequest::getVar('staff_id', '');

		$this->list_departments = $this->createListOfDepartments();
		$this->list_staffs = $this->createListOfStaffs();
		$this->list_customers = $this->createListOfcustomer();
	}

	function createListOfDepartments($user_id=NULL){
		$db = JFactory::getDBO();
		$arr_obj = array();

		//fix bug: error occur when $user_id =NULL
		if($user_id==NULL){
			$query  = " SELECT * FROM `#__obhelpdesk3_staff_department` as sd";
		}else{
			$query  = " SELECT * FROM `#__obhelpdesk3_staff_department` as sd"
				. " WHERE sd.user_id =".$user_id;
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$arr_value = array();
		$arr_obj[] 	= JHTML::_('select.option', '0', JText::_('OBHELPDESK_REPORT_ALL_OF_DEPARTMENT'));
		if(count($rows)){
			foreach ($rows as $v){
				$arr_value[] = $v->department_id;
			}
		}

		$query = "SELECT title as text, id as value FROM `#__obhelpdesk3_departments`";
		$db->setQuery($query);
		$rows1 = $db->loadObjectList();
		if(count($rows1)){
			foreach ($rows1 as $row){
				$obj = new stdClass();
				$obj->text = $row->text;
				$obj->value = $row->value;
				array_push($arr_obj, $obj);
			}
		}

		$size = count($rows1) + 3;

		if($this->selectedDepartment != ''){
			$list = JHTML::_('select.genericlist',  $arr_obj, 'department_id[]', 'class="inputbox" size="'.$size.'" multiple="true" ', 'value', 'text', $this->selectedDepartment);
		}else{
			$list = JHTML::_('select.genericlist',  $arr_obj, 'department_id[]', 'class="inputbox" size="'.$size.'" multiple="true" ', 'value', 'text', 0);
		}

		return $list;

	}
	function createListOfStaffs()
	{
		$db 	= JFactory::getDBO();
		$query 	= "SELECT b.name, b.username,  a.user_id as value FROM `#__obhelpdesk3_staffs` as a";
		$query .= " LEFT JOIN `#__users` as b ON a.user_id = b.id";
		$db->setQuery($query);
		$rows 	= $db->loadObjectList();

		if(count($rows)){
			foreach ($rows as $v){
				$arr_value[] = $v->value;
			}
		}

		$arr_obj 	= array();
		$arr_obj[] 	= JHTML::_('select.option', '0', JText::_('OBHELPDESK_REPORT_ALL_OF_STAFF'));
		if (count($rows)) {
			foreach ($rows as $row) {
				$obj = new stdClass();
				$obj->text = $row->username.'('.$row->name.')';
				$obj->value = $row->value;
				array_push($arr_obj, $obj);
			}
		}
		if($this->selectedStaff != ''){
			$lists = JHTML::_('select.genericlist',  $arr_obj, 'staff_id[]', 'class="inputbox" size="7" multiple="true"', 'value', 'text', $this->selectedStaff);
		}else{
			$lists = JHTML::_('select.genericlist',  $arr_obj, 'staff_id[]', 'class="inputbox" size="7" multiple="true"', 'value', 'text', 0);
		}

		return $lists;
	}
	function createListOfcustomer()
	{

	}

	/**
	 *
	 * numOfTickets ...
	 */

	public function getTicketCountData()
	{
		$db 	= JFactory::getDBO();

		if($this->calendar_unit == "day"){
			$select = 'SELECT Date(`created`) as OBHELPDESK_REPORT_CREATEDDATE, count(*) as OBHELPDESK_REPORT_NUMBEROFTICKETS FROM `#__obhelpdesk3_tickets` ';
			$groupby = ' group by Date(`created`) ';
			$orderby = ' Order by OBHELPDESK_REPORT_CREATEDDATE';
		}else{
			$select = 'SELECT Month(`created`) as OBHELPDESK_REPORT_CREATEDMONTH, count(*) as OBHELPDESK_REPORT_NUMBEROFTICKETS FROM `#__obhelpdesk3_tickets` ';
			$groupby = ' group by Date(`created`) ';
			$orderby = ' Order by OBHELPDESK_REPORT_CREATEDMONTH';
		}

		#TODO : set where here
		$field_counter = 0;
		$allDepart = false;
		$list_dep = '';
		foreach ($this->selectedDepartment as $id=> $dep_id){
			if($dep_id == 0) $allDepart = true;
			if($field_counter==0){
				$list_dep .= $dep_id;
				$field_counter++;
			}else{
				$list_dep .=  ','. $dep_id;
			}
		}
		$list_dep = '('.$list_dep.')';
		$where = " WHERE Date(`created`) >= '".$this->calendar_from."' AND Date(`created`) <='". $this->calendar_to."'";
		#depart
		if(!$allDepart){
			$where .= " AND `department_id` IN ". $list_dep;
		}
		$query = $select.$where.$groupby.$orderby;

		$db->setQuery( $query );
		$this->data = $db->loadObjectList();
		#return $db->loadObjectList();
	}
	/**
	 *
	 * numOfTickets ...
	 * tam thoi lay du lieu giong nhu grap
	 */
	public function getTicketCountData_Table()
	{
	}

	/**
	 *
	 * numOfTickets_2 ...
	 */
	public function getTicketCountData_2()
	{
		$db 	= JFactory::getDBO();
		$select = 'SELECT name as OBHELPDESK_REPORT_DEPARTMENT, count(*) as OBHELPDESK_REPORT_NUMBEROFTICKETS FROM `#__obhelpdesk3_tickets` as T join `#__obhelpdesk3_departments` as D on T.`department_id` = D.`id` ';
		$groupby = ' group by T.`department_id` ';
		$orderby = ' Order by T.`department_id` ';


		#TODO : set where here
		$field_counter = 0;
		$allDepart = false;
		$list_dep='';
		foreach ($this->selectedDepartment as $id=> $dep_id){
			if($dep_id == 0) $allDepart = true;
			if($field_counter==0){
				$list_dep .= $dep_id;
				$field_counter++;
			}else{
				$list_dep .=  ','. $dep_id;
			}
		}
		$list_dep = '('.$list_dep.')';
		#date
		$where = " WHERE Date(T.`created`) >= '".$this->calendar_from."' AND Date(T.`created`) <='". $this->calendar_to."'";
		#depart
		if(!$allDepart){
			$where .= " AND `department_id` IN ". $list_dep;
		}
		$query = $select.$where.$groupby.$orderby;

		$db->setQuery( $query );
		$this->data = $db->loadObjectList();
		#return $db->loadObjectList();
	}
	/**
	 *
	 * numOfTickets_2 ...
	 * tam thoi lay du lieu giong nhu grap
	 */
	public function getTicketCountData_Table_2()
	{

	}

	/**
	 *
	 * Spent time charts
	 * spent time cua toan bo he thong theo ngay thang
	 */
	public function getSpentTimeData()
	{
		$db 	= JFactory::getDBO();
		$select = 'SELECT  Date(`reply_time`) as OBHELPDESK_REPORT_DATE, SUM(`ticket_time` ) as OBHELPDESK_REPORT_SPENTTIME  FROM  `#__obhelpdesk3_ticket_message` AS M JOIN  `#__users` AS U ON M.`user_id` = U.`id` ';
		$groupby = ' group by Date(`reply_time`) ';
		$orderby = ' Order by Date(`reply_time`)  ';


		#TODO : giai quyet dieu kien department.

		$field_counter = 0;
		$allUser = false;
		$list_staff='';
		foreach ($this->selectedStaff as $id=> $staff_id){
			if($staff_id == 0) $allUser = true;
			if($field_counter==0){
				$list_staff .= $staff_id;
				$field_counter++;
			}else{
				$list_staff .=  ','. $staff_id;
			}
		}
		$list_staff = '('.$list_staff.')';
		$where = " WHERE `ticket_time` > 0 AND Date(`reply_time`) >= '".$this->calendar_from."' AND Date(`reply_time`) <='". $this->calendar_to."'";
		#user
		if(!$allUser){
			$where .= " AND `user_id` IN". $list_staff;
		}

		$query = $select.$where.$groupby.$orderby;

		$db->setQuery( $query );
		$this->data = $db->loadObjectList();
	}

	/**
	 *
	 * Spent time charts
	 */
	public function getSpentTimeData_2()
	{
		$db 	= JFactory::getDBO();
		$select = 'SELECT  U.Name as OBHELPDESK_REPORT_USERNAME, SUM(`ticket_time` ) as OBHELPDESK_REPORT_SPENTTIME  FROM  `#__obhelpdesk3_ticket_message` AS M JOIN  `#__users` AS U ON M.`user_id` = U.`id` ';
		$groupby = ' group by `user_id` ';
		$orderby = ' Order by `user_id`  ';


		#TODO : giai quyet dieu kien department.
		$field_counter = 0;
		$allDepart = false;
		$list_dep='';
		foreach ($this->selectedDepartment as $id=> $dep_id){
			if($dep_id == 0) $allDepart = true;
			if($field_counter==0){
				$list_dep .= $dep_id;
				$field_counter++;
			}else{
				$list_dep .=  ','. $dep_id;
			}
		}
		$list_dep = '('.$list_dep.')';

		$field_counter = 0;
		$allUser = false;
		$list_staff='';
		foreach ($this->selectedStaff as $id=> $staff_id){
			if($staff_id == 0) $allUser = true;
			if($field_counter==0){
				$list_staff .= $staff_id;
				$field_counter++;
			}else{
				$list_staff .=  ','. $staff_id;
			}
		}
		$list_staff = '('.$list_staff.')';
		$where = " WHERE `ticket_time` > 0 AND Date(`reply_time`) >= '".$this->calendar_from."' AND Date(`reply_time`) <='". $this->calendar_to."'";
		#depart
		if(!$allDepart){
			$where .= " AND `department_id` IN ". $list_dep;
		}
		#user
		if(!$allUser){
			$where .= " AND `user_id` IN". $list_staff;
		}

		$query = $select.$where.$groupby.$orderby;

		$db->setQuery( $query );
		$this->data = $db->loadObjectList();

	}
}