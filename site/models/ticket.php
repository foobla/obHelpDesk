<?php

defined( '_JEXEC' ) or die;

// import Joomla modelform library
jimport( 'joomla.application.component.modeladmin' );
/**
 * Methods supporting a list of department records.
 */
class obHelpDeskModelTicket extends JModelAdmin {
	/**
	 * Returns a reference to the a Table object, always creating it.
	 */
	public function getTable( $type = 'Ticket', $prefix = 'obHelpDeskTable', $config = array() ) {
		return JTable::getInstance( $type, $prefix, $config );
	}

	public function getItem( $pk = null ) {
		$session = JFactory::getSession();
		$tid     = ( $session->get( 'obhelpdesk_tid' ) ) ? $session->get( 'obhelpdesk_tid' ) : 0;

		return parent::getItem( $tid );
	}

	/**
	 * Method to get the record form.
	 */
	public function getForm( $data = array(), $loadData = true ) {
		// Get the form.
		$form = $this->loadForm( 'com_obhelpdesk.ticket', 'ticket', array( 'control' => 'jform', 'load_data' => $loadData ) );
		if ( empty( $form ) ) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 * @since    1.6
	 */
	protected function loadFormData() {
		$session = JFactory::getSession();
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState( 'com_obhelpdesk.edit.reply.data', array() );
		if ( empty( $data ) ) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Get list of messages/replies from a ticket
	 *
	 * @param     $tid
	 * @param int $poster 0: both staff & customer; 1: only staff; 2: only customer
	 *
	 * @return bool|mixed
	 */
	function getMessages( $tid, $poster = 0 ) {
		$user = JFactory::getUser();
		if ( (int) $tid == 0 ) {
			return false;
		}

		$db = JFactory::getDbo();
		if ( $poster == 0 ) { // both staff & customer

		} elseif ( $poster == 1 ) { // only staff
			$from  = '';
			$where = '';
		} else { // $poster == 2, only customer
			$from  = '';
			$where = '';
		}

		$query = "SELECT m.*, u.email as umail, u.name as uname, oc.email as cmail, oc.fullname as cname"
			. " FROM `#__obhelpdesk3_messages` as m"
			. " LEFT JOIN `#__users` as u ON m.`user_id` = u.`id`"
			. " LEFT JOIN `#__obhelpdesk3_customers` as oc ON m.`email` = oc.`email`"
			. " WHERE m.`tid`=" . $tid . " ORDER BY `id` DESC
						LIMIT 5";

		$db->setQuery( $query );

		return $db->loadObjectList();
	}

	/**
	 * Get last updated canned response for a specific level
	 *
	 * @ticket_id int ticket_id
	 * @return mixed
	 * @todo      think about getting random canned response
	 */
	function get_canned_response( $ticket_id ) {
		$user     = JFactory::getUser();

		$db = JFactory::getDbo();

		// counting the replies by staff to get the canned response level
		$sql = "
			SELECT
				COUNT(m.id)
			FROM
				`#__obhelpdesk3_messages` AS m,
				`#__obhelpdesk3_staffs` AS s
			WHERE
				s.`user_id` = m.`user_id` AND
				m.`user_id` = {$user->id} AND
				m.`tid` = {$ticket_id}
		";
		$db->setQuery( $sql );
		$level = $db->loadResult() + 1;
//		echo '<br />level: ' . $level;

		// get max level for canned response
		$sql = "
			SELECT
				MAX(cr.`level`)
			FROM
				`#__obhelpdesk3_replytemplates` AS cr
			WHERE
				cr.`enable` = 1 AND
				cr.`published` = 1 AND
				cr.`staff_id` = {$user->id}
		";
		$db->setQuery( $sql );
		$max_level = $db->loadResult();
//		echo '<br />max_level: ' . $max_level;

		// get the canned response (CR) content, if there no configured CR for this level, use the max level CR.
		// if there are more than one CR in the same level, it will get the last modified one
		if( $max_level ) {
			$sql = "
				SELECT
					`content`
				FROM
					`#__obhelpdesk3_replytemplates`
				WHERE
					`staff_id` = {$user->id} AND
					( `level` = {$level} OR `level` =  {$max_level} )
				LIMIT 1
			";
			$db->setQuery( $sql );
			$content = $db->loadResult();
		}else{
			$content = '';
		}

		return $content;
	}

	function getDepartmentList() {
		$item = $this->getItem();
// 		echo '<pre>'.print_r( $item, true ).'</pre>';
		$value    = $item->departmentid;
		$app      = JFactory::getApplication();
		$user     = JFactory::getUser();
		$dids     = array();
		$is_staff = obHelpDeskUserHelper::is_staff( $user->id );
		if ( $user->id && $is_staff ) {
			$dids = obHelpDeskUserHelper::getStaffDepartment( $user->id );
		} else {
			$department = obHelpDeskTicketHelper::getDepartment( $item->departmentid );

			return $department->title;
		}

		$db = JFactory::getDbo();
		if ( count( $dids ) ) {
			$query = "SELECT `title` as text, `id` as value FROM `#__obhelpdesk3_departments` WHERE `id` IN (" . implode( ',', $dids ) . ") AND `published`=1";
			$db->setQuery( $query );
			$rows    = $db->loadObjectList();
			$arr_obj = array();
			if ( count( $rows ) ) {
				foreach ( $rows as $row ) {
					$obj        = new stdClass();
					$obj->text  = $row->text;
					$obj->value = $row->value;
					array_push( $arr_obj, $obj );
				}
			}
			$javascript = ' onchange="loadfields(this.form);"';

			return JHTML::_( 'select.genericlist', $arr_obj, 'jform[departmentid]', 'class="inputbox span3"' . $javascript, 'value', 'text', $value );
		} else {
			return false;
		}
	}
}
