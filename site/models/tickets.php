<?php
/**
* @package		$Id: tickets.php 58 2013-09-06 01:27:09Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');
/**
 * Methods supporting a list of department records.
 */
class obHelpDeskModelTickets extends JModelList
{
	public $dids = array();
	/**
	 * Constructor.
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'staff', 'a.staff',
				'customer_id', 'a.customer_id',
				'customer_email', 'a.customer_email',
				'customer_fullname', 'a.customer_fullname',
				'departmentid', 'a.departmentid',
				'subject', 'a.subject',
				'quickcode', 'a.quickcode',
				'status', 'a.status',
				'priority', 'a.priority',
				'created', 'a.created',
				'updated', 'a.updated',
				'auto_close_sent', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'first_msg_id', 'a.first_msg_id',
				'last_msg_id', 'a.last_msg_id',
				'info', 'a.info',
				'code', 'concat(d.prefix, "-", a.id)',
				'priority_name', 'p.title as priority_name',
				'priority_ordering', 'p.ordering as priority_ordering',
				'reply_time', 'm.reply_time',
				'last_message', 'm.content',
				'email_reply', 'm.email',
				'uid_reply', 'm.user_id',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Adjust the context to support modal layouts.
		if ($layout = JRequest::getVar('layout', 'default'))
		{
			$this->context .= '.'.$layout;
		}

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published');
		$this->setState('filter.published', $state);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_obhelpdesk');
		$this->setState('params', $params);
		
		// List state information - by default, we will ordering tickets by last message, newest on top
		parent::populateState('m.reply_time', 'DESC');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$session	= JFactory::getSession();
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		$is_staff = false;
		$see_other_tickets = false;
		$see_unassigned_tickets = false;
		// check permission 
		if($user->id) {
			$is_staff = obHelpDeskUserHelper::is_staff($user->id) ;
			if($is_staff) {
				$see_other_tickets = obHelpDeskUserHelper::checkPermission($user->id, 'see_other_tickets');
				$see_unassigned_tickets = obHelpDeskUserHelper::checkPermission($user->id, 'see_unassigned_tickets');;
			}
		}

		$overduetime	= obHelpDeskHelper::getConfig('overduetime')->value;
		if( !$overduetime ) $overduetime = 1440;
		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'`a`.`id`,' 
				.'`a`.`staff`, '
				.'`a`.`customer_id`, '
				.'`a`.`customer_email`, '
				.'`a`.`customer_fullname`,' 
				.'`a`.`departmentid`, '
				.'`a`.`quickcode`, '
				.'`a`.`subject`, '
				.'`a`.`status`, '
				.'`a`.`priority`, '
				.'`a`.`created`, '
				.'`a`.`updated`, '
				.'`a`.`auto_close_sent`, '
				.'`a`.`first_msg_id`, '
				.'`a`.`last_msg_id`, '
				.' concat(de.`prefix`, "-", a.`id`) AS code,'
				.' de.`prefix` AS `prefix`, '
				.' de.`label_color` AS `label_color`, '
				.' p.title AS priority_name,'
				.' p.color AS priority_color,'
				.' p.ordering AS priority_ordering,'
				.' m.reply_time,'
				.' m.content AS last_message,'
				.' m.email AS email_reply,'
				.' m.user_id AS uid_reply,'
				.' m2.content AS `message`,'
				.' if (TIMESTAMPDIFF(MINUTE,m.reply_time,now())>='.$overduetime.', 1, 0) AS `overdue`, '
				.' `a`.`replies`'
			)
		);

		$query->from($db->quoteName('#__obhelpdesk3_tickets').' AS a');
		$query->join('INNER', '#__obhelpdesk3_departments AS de ON ( de.id = a.departmentid AND de.published=1 )' );
		$query->join('INNER', '#__obhelpdesk3_priority AS p ON p.id = a.priority');
		$query->join('INNER', '#__obhelpdesk3_messages AS m ON (m.tid = a.id AND m.id = a.last_msg_id)');
		$query->join('INNER', '#__obhelpdesk3_messages AS m2 ON (m2.tid = a.id AND m2.id = a.last_msg_id)');

		$email = ($user->email)? $user->email : '';
		if(!$is_staff) {
			$query->where('a.customer_email = "'.$user->email.'"');
		} else {
			//is Staff
			$this->dids = obHelpDeskUserHelper::getStaffDepartment($user->id);
			// only Show ticket in these departments
			$query->where('a.departmentid IN('.implode(',', $this->dids).')'); 
			if( !$see_unassigned_tickets ) 
				$query->where('((a.staff != 0) OR (a.customer_email = "'.$user->email.'" OR a.staff='.$user->id.'))');
			if( !$see_other_tickets ) 
				$query->where('((a.staff = 0) OR (a.customer_email = "'.$user->email.'" OR a.staff='.$user->id.'))');
		}
		/*** FILTER ****/

		//$did = JRequest::getVar('filter_department', 0);
		$did = $app->getUserStateFromRequest( "com_obhelpdesk.filter_department", 'filter_department', 0 );

		if($did) {
			$query->where('a.departmentid = '.$did);
		}

		//$staffid = JRequest::getVar('filter_staff', 'all');
		$staffid = $app->getUserStateFromRequest( "com_obhelpdesk.filter_staff", 'filter_staff', 'all' );

		if($staffid != 'all') {
			$staff = JFactory::getUser($staffid);
			$query->where('(a.staff = '.$staffid.' OR a.customer_email = "'.$staff->email.'")');
		}

		//$status = JRequest::getVar('filter_status', 'open');
		$status = $app->getUserStateFromRequest( "com_obhelpdesk.filter_status", 'filter_status', 'open' );
		if($status) {
			if($status == 'all') {
				$query->where('(a.status = "open" OR a.status = "on-hold" OR a.status="closed")');
			} else {
				$query->where('a.status = "'.$status.'"');
			}
		}
		
// 		$filter_from		= JRequest::getVar('filter_from');
// 		$filter_to			= JRequest::getVar('filter_to');
		$filter_from		= $app->getUserStateFromRequest( "com_obhelpdesk.filter_from", 'filter_from', '' );
		$filter_to			= $app->getUserStateFromRequest( "com_obhelpdesk.filter_to", 'filter_to', '' );
		
		if ($filter_from) {
			if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $filter_from)) {
				$from = JFactory::getDate($filter_from.' 00:00:00')->toSql();
				$query->where('a.`created` >= '.$db->Quote($from));
			}
		}
		
		if ($filter_to) {
			if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $filter_to)) {
				$to = JFactory::getDate($filter_to.' 23:59:59')->toSql();
				$query->where('a.`created` <= '.$db->Quote($to));
			}
		}
		
		// Filter the items over the search string if set.
		if ($this->getState('filter.search') !== '')
		{
			// Escape the search token.
			$token	= $db->Quote('%'.$db->escape($this->getState('filter.search')).'%');

			// Compile the different search clauses.
			$searches	= array();
			$searches[]	= 'a.subject LIKE '.$token;

			// Add the clauses to the query.
			$query->where('('.implode(' OR ', $searches).')');
		}
		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.subject')).' '.$db->escape($this->getState('list.direction', 'ASC')));
		return $query;
	}
	
	function getCountReplies($tid) {
		$db = JFactory::getDbo();
		$query = "SELECT COUNT(*) FROM `#__obhelpdesk3_messages` WHERE `tid`=".(int)$tid;
		$db->setQuery($query);
		$result = $db->loadResult();
		if($result) return ( $result - 1 );
		return 0;
	}
	
	function getFilterDepartmentList(){
		$app 	= JFactory::getApplication();
		$user 	= JFactory::getUser();
// 		$department_id = JRequest::getVar('filter_department', 0);
		$department_id = $app->getUserStateFromRequest( "com_obhelpdesk.filter_department", 'filter_department', 0 );
		$db = JFactory::getDbo();
		if(count($this->dids)) {
			$query = "SELECT `title` as text, `id` as value FROM `#__obhelpdesk3_departments` WHERE `id` IN (".implode(',', $this->dids).") AND `published`=1 ORDER BY `ordering` ASC";
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			
			$arr_obj[] = JHTML::_('select.option', '0', JText::_('SELECT_DEPARTMENT'));
			
			if(count($rows)){
				foreach ($rows as $row){
					$obj = new stdClass();
					$obj->text = $row->text;
					$obj->value = $row->value;
					array_push($arr_obj, $obj);
				}
			}
			$javascript = ' onchange="adminForm.submit();"';
			return JHTML::_('select.genericlist',  $arr_obj, 'filter_department', 'class="inputbox span3"'.$javascript, 'value', 'text', $department_id);
		} else {
			return false;
		}
	}
	
	function getFilterStaffList(){
		$user 		= JFactory::getUser();
		$staff_id 	= JRequest::getVar('filter_staff', 'all');
		$db = JFactory::getDbo();
		if(count($this->dids)) {
			$query = "SELECT DISTINCT a.`user_id` FROM `#__obhelpdesk3_staff_department` as a"
					." INNER JOIN `#__obhelpdesk3_staffs` as b ON a.`user_id` = b.`user_id`"
					." WHERE a.`department_id` IN (".implode(',', $this->dids).")";
			$db->setQuery($query);
			$user_ids = $db->loadColumn();
			if(!count($user_ids)) return false;
			$query = "SELECT `name` as text, `id` as value FROM `#__users` WHERE `id` IN (".implode(',', $user_ids).") AND `block` = 0";
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			
			$arr_obj[] = JHTML::_('select.option', 'all', JText::_('SELECT_STAFF'));
			$arr_obj[] = JHTML::_('select.option', '0', JText::_('OBHELPDESK_UNASSIGNED'));
			if(count($rows)){
				foreach ($rows as $row){
					$obj = new stdClass();
					$obj->text = $row->text;
					$obj->value = $row->value;
					array_push($arr_obj, $obj);
				}
			}
			$javascript = ' onchange="adminForm.submit();"';
			return JHTML::_('select.genericlist',  $arr_obj, 'filter_staff', 'class="inputbox span2"'.$javascript, 'value', 'text', $staff_id);
		} else {
			return false;
		}
	}
	
	function getFilterStatusList($default_status='open'){
		$user = JFactory::getUser();
		$status = $default_status;
		//$status = JRequest::getVar('filter_status', 'open');
		$arr_status = array (
			'open'		=> JText::_('COM_OBHELPDESK_STATUS_OPEN'),
			'closed'	=> JText::_('COM_OBHELPDESK_STATUS_CLOSED'),
			'on-hold'	=> JText::_('COM_OBHELPDESK_STATUS_ONHOLD')
		);
		$arr_obj[] = JHTML::_('select.option', 'all', JText::_('SELECT_STATUS'));
		
		foreach ($arr_status as $key => $text){
			$obj = new stdClass();
			$obj->text = $text;
			$obj->value = $key;
			array_push($arr_obj, $obj);
		}
		$javascript = ' onchange="adminForm.submit();"';
		return JHTML::_('select.genericlist',  $arr_obj, 'filter_status', 'class="inputbox span2"'.$javascript, 'value', 'text', $status);
	}
	
	function getBulkPriority() {
		$db = JFactory::getDbo();
		$query = "SELECT `title` as text, `id` as value, `color` FROM `#__obhelpdesk3_priority` WHERE `published` = 1 ORDER BY `ordering` ASC";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$arr_obj[] = JHTML::_('select.option', '0', JText::_('SELECT_PRIORITY'));
		
		if(count($rows)){
			foreach ($rows as $row){
				$obj = new stdClass();
				$obj->text = $row->text;
				$obj->value = $row->value;
				array_push($arr_obj, $obj);
			}
		}
		
		$javascript = ' onchange="if(document.adminForm.operator.value > 0) document.adminForm.operator.value++; else document.adminForm.operator.value--;"';
		return JHTML::_('select.genericlist',  $arr_obj, 'bulk_priority', 'class="inputbox"'.$javascript, 'value', 'text');
	}
	
	function getBulkStatus(){
		$status = array(
			'open'		=> JText::_('COM_OBHELPDESK_STATUS_OPEN'),
			'closed'	=> JText::_('COM_OBHELPDESK_STATUS_CLOSED'),
			'on-hold'	=> JText::_('COM_OBHELPDESK_STATUS_ONHOLD')
		);
		
		$arr_obj[] = JHTML::_('select.option', '', JText::_('SELECT_STATUS'));
		
		foreach ($status as $key => $text){
			$obj = new stdClass();
			$obj->value = $key;
			$obj->text = $text;
			array_push($arr_obj, $obj);
		}
		
		$javascript = ' onchange="if(document.adminForm.operator.value > 0) document.adminForm.operator.value++; else document.adminForm.operator.value--;"';
		return JHTML::_('select.genericlist',  $arr_obj, 'bulk_status', 'class="inputbox"'.$javascript, 'value', 'text');
	}
	
	public function getPagination()
	{
		$jv = new JVersion();
		if($jv->RELEASE=='2.5'){
			// Get a storage key.
			$store = $this->getStoreId('getPagination');
		
			// Try to load the data from internal storage.
			if (isset($this->cache[$store]))
			{
				return $this->cache[$store];
			}
			jimport('joomla.html.pagination');
	// 		jimport('joomla.pagination.pagination');
			// Create the pagination object.
			$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
			require_once JPATH_COMPONENT.'/helpers/obpagination.php';
			$page = new obPagination($this->getTotal(), $this->getStart(), $limit);
		
			// Add the object to the internal cache.
			$this->cache[$store] = $page;
		
			return $this->cache[$store];
		}else{
			return parent::getPagination();
		}
	}
}
