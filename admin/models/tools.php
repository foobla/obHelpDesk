<?php
/**
* @package		$Id: tools.php 18 2013-08-06 10:30:40Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'obhelpdesk.php';
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.archive.zip');

/**
 * Config Model class
 */

class obHelpDeskModelTools extends JModelAdmin
{

	/**
	 * Method to get the record form.
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_obhelpdesk.tools', 'tools', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	
	public function import_obhd() {
		$app 	= JFactory::getApplication();
		$db 	= JFactory::getDbo();
		$sql = 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";';
		$db->setQuery($sql);
		$db->query();
		if($db->getErrorNum()){
			$app->enqueueMessage($db->getErrorMsg(),'error');
		}
		# CLEAR DATA ON OBHELPDESK 3
		$sql = 'TRUNCATE TABLE #__obhelpdesk3_customer_care';
		$db->setQuery($sql);
		$db->query();
		$sql = 'TRUNCATE TABLE #__obhelpdesk3_customers';
		$db->setQuery($sql);
		$db->query();
		$sql = 'TRUNCATE TABLE #__obhelpdesk3_departments';
		$db->setQuery($sql);
		$db->query();
		$sql = 'TRUNCATE TABLE #__obhelpdesk3_field_values';
		$db->setQuery($sql);
		$db->query();
		$sql = 'TRUNCATE TABLE #__obhelpdesk3_fields';
		$db->setQuery($sql);
		$db->query();
		$sql = 'TRUNCATE TABLE #__obhelpdesk3_groups';
		$db->setQuery($sql);
		$db->query();
		$sql = 'TRUNCATE TABLE #__obhelpdesk3_messages';
		$db->setQuery($sql);
		$db->query();
		$sql = 'TRUNCATE TABLE #__obhelpdesk3_priority';
		$db->setQuery($sql);
		$db->query();
		$sql = 'TRUNCATE TABLE #__obhelpdesk3_replytemplates';
		$db->setQuery($sql);
		$db->query();
		$sql = 'TRUNCATE TABLE #__obhelpdesk3_staff_department';
		$db->setQuery($sql);
		$db->query();
		$sql = 'TRUNCATE TABLE #__obhelpdesk3_staffs';
		$db->setQuery($sql);
		$db->query();
		$sql = 'TRUNCATE TABLE #__obhelpdesk3_tickets';
		$db->setQuery($sql);
		$db->query();
		
		# IMPORT CUSTOMER_CARE
		$sql = "INSERT INTO `#__obhelpdesk3_customer_care`
				(	`id`,
					`customer_id`,
					`staff_id_list`,
					`notify_email`,
					`published`,
					`checked_out`,
					`checked_out_time`)
				SELECT
					`id`,
					`customer_id`,
					`staff_id_list`,
					`notify_email`,
					`published`,
					`checked_out`,
					`checked_out_time`
				FROM `#__obhelpdesk_customer_care`";
		$db->setQuery($sql);
		$db->query();
		if($db->getErrorNum()){
			$app->enqueueMessage($db->getErrorMsg(),'error');
		}
		
		# IMPORT DEPARTMENTS
		$sql = "INSERT INTO `#__obhelpdesk3_departments`
				(
					`id`,
					`title`,
					`description`,
					`prefix`,
					`kb_catid`,
					`assignment_type`,
					`generation_rule`,
					`next_ticket_number`,
					`user_email_ticket`,
					`staff_email_ticket`,
					`file_upload`,
					`notify_new_ticket_emails`,
					`notify_assign`,
					`file_upload_extensions`,
					`priority`,
					`ordering`,
					`checked_out`,
					`checked_out_time`,
					`published`,
					`usergroups`,
					`fields`,
					`label_color`)
				SELECT
					d.`id`,
					d.`name` AS `title`,
					d.`description`,
					d.`prefix`,
					d.`kb_catid`,
					d.`assignment_type`,
					d.`generation_rule`,
					d.`next_ticket_number`,
					d.`user_email_ticket`,
					d.`staff_email_ticket`,
					d.`file_upload`,
					d.`notify_new_ticket_emails`,
					d.`notify_assign`,
					d.`file_upload_extensions`,
					d.`priority`,
					d.`ordering`,
					d.`checked_out`,
					'0000-00-00 00:00:00' AS `checked_out_time`,
					d.`published`,
					1 AS `usergroups`,
					( SELECT group_concat( `customfield_id` separator ',') FROM #__obhelpdesk_customfield_department  WHERE department_id=d.id group by `department_id`) AS `fields`,
					'' AS `label_color`
				FROM `#__obhelpdesk_departments` as d";
		$db->setQuery($sql);
		$db->query();
		if($db->getErrorNum()){
			$app->enqueueMessage($db->getErrorMsg(),'error');
		}
		
		
		# FIELDS
		$sql = "INSERT INTO `#__obhelpdesk3_fields`
				(
					`id`,
					`name`,
					`title`,
					`type`,
					`published`,
					`helptext`,
					`values`,
					`required`,
					`ordering`,
					`checked_out`)
				SELECT
					`id`,
					`name`,
					`label` AS `title`,
					CASE `type` 
						WHEN 'select' THEN 'list' 
						WHEN 'textbox' THEN 'text' END AS `type`,
					1 AS `published`,
					`description` AS `helptext`,
					`values`,
					`required`,
					`ordering`,
					`checked_out`
				FROM `#__obhelpdesk_custom_fields`";
		$db->setQuery($sql);
		$db->query();
		if($db->getErrorNum()){
			$app->enqueueMessage($db->getErrorMsg(),'error');
		}
		
		
		# FIELD VALUES
		$sql = "INSERT INTO `#__obhelpdesk3_field_values`
				(
					`id`,
					`field_id`,
					`department_id`,
					`ticket_id`,
					`value`)
				SELECT
					`id`,
					`custom_field_id` AS `field_id`,
					`department_id`,
					`ticket_id`,
					`custom_field_value` AS `value`
				FROM `#__obhelpdesk_custom_fields_value`";
		$db->setQuery($sql);
		$db->query();
		if($db->getErrorNum()){
			$app->enqueueMessage($db->getErrorMsg(),'error');
		}
		
		
		# GROUPS
		
		$sql = "INSERT INTO `#__obhelpdesk3_groups`
				(
					`id`,
					`title`,
					`published`,
					`add_ticket`,
					`add_ticket_staffs`,
					`add_ticket_users`,
					`update_ticket`,
					`delete_ticket`,
					`answer_ticket`,
					`delete_ticket_replies`,
					`update_ticket_replies`,
					`assign_tickets`,
					`change_ticket_status`,
					`see_other_tickets`,
					`see_unassigned_tickets`,
					`move_ticket`,
					`reopen_ticket`,
					`add_monitoring`,
					`checked_out`,
					`checked_out_time`)
				SELECT
					`id`,
					`name` AS `title`,
					1 AS `published`,
					`add_ticket`,
					`add_ticket_staff` AS `add_ticket_staffs`,
					`add_ticket_users`,
					`update_ticket`,
					`delete_ticket`,
					`answer_ticket`,
					`delete_ticket_replies`,
					`update_ticket_replies`,
					`assign_tickets`,
					`change_ticket_status`,
					`see_other_tickets`,
					`see_unallocated_tickets` AS `see_unassigned_tickets`,
					`move_ticket`,
					`reopen_ticket`,
					`add_monitoring`,
					`checked_out`,
					'0000-00-00 00:00:00' AS `checked_out_time`
				FROM `#__obhelpdesk_groups`";
		$db->setQuery($sql);
		$db->query();
		if($db->getErrorNum()){
			$app->enqueueMessage($db->getErrorMsg(),'error');
		}
		
		
		# IMPORT STAFF
		$sql = "INSERT INTO `#__obhelpdesk3_staffs`
				(
					`id`,
					`group_id`,
					`user_id`,
					`checked_out`,
					`checked_out_time`)
				SELECT
					`id`,
					`group_id`,
					`user_id`,
					`checked_out`,
					'0000-00-00 00:00:00' AS `checked_out_time`
				FROM `#__obhelpdesk_staff`";
		$db->setQuery($sql);
		$db->query();
		if($db->getErrorNum()){
			$app->enqueueMessage($db->getErrorMsg(),'error');
		}
		
		# IMPORT PRIORITY
		$sql = "INSERT INTO `#__obhelpdesk3_priority`
				(
					`id`,
					`title`,
					`alias`,
					`published`,
					`color`,
					`ordering`,
					`checked_out`)
				SELECT
					`id`,
					`label` AS `title`,
					`alias`,
					1 AS `published`,
					`color`,
					`ordering`,
					`checked_out`
				FROM `#__obhelpdesk_priority`";
		$db->setQuery($sql);
		$db->query();
		
		#IMPORT REPLYTEMPLATE
		$sql = "INSERT INTO `#__obhelpdesk3_replytemplates`
				(
					`id`,
					`subject`,
					`content`,
					`staff_id`,
					`enable`,
					`published`,
					`default`,
					`ordering`,
					`modified_date`,
					`created_date`,
					`copy_from`,
					`checked_out`,
					`checked_out_time`)
				SELECT
					`id`,
					`subject`,
					`content`,
					`staff_id`,
					`enable`,
					`published`,
					`default`,
					`ordering`,
					`modified_date`,
					`created_date`,
					`copy_from`,
					`checked_out`,
					`checked_out_time`
				FROM `#__obhelpdesk_templates`";
		$db->setQuery($sql);
		$db->query();
		if($db->getErrorNum()){
			$app->enqueueMessage($db->getErrorMsg(),'error');
		}
		
		
		# STAFF DEPARTMENT
		$sql ="INSERT INTO `#__obhelpdesk3_staff_department`
				(
					`id`,
					`user_id`,
					`department_id`,
					`open_tickets`)
				SELECT
					`id`,
					`user_id`,
					`department_id`,
					`open_tickets`
				FROM `#__obhelpdesk_staff_to_department`";
		$db->setQuery($sql);
		$db->query();
		if($db->getErrorNum()){
			$app->enqueueMessage($db->getErrorMsg(),'error');
			exit();
		}
		
		
		#IMPORt MESSAGE
		$sql ="INSERT INTO `#__obhelpdesk3_messages` 
				(
					`id`, 
					`tid`, 
					`user_id`, 
					`email`, 
					`content`, 
					`reply_time`, 
					`files`)
				SELECT 
					m.`id`,
					m.`ticket_id` AS `tid`,
					m.`user_id`,
					u.`email`,
					m.`ticket_message`,
					m.`reply_time`,
					m.`files`
				FROM
					`#__obhelpdesk_ticket_message` AS m
						INNER JOIN
					`#__users` AS u ON m.user_id = u.id";
		$db->setQuery($sql);
		$db->query();
		if($db->getErrorNum()){
			$app->enqueueMessage($db->getErrorMsg(),'error');
			return;
		}
		
		# IMPORT TICKET
		// create temp table
		$sql = "CREATE TABLE IF NOT EXISTS #__obhelpdesk3_temp1 (
					tid int,
					first_msg_id int,
					last_msg_id int
				)";
		$db->setQuery($sql);
		$db->query();
		if($db->getErrorNum()){
			$app->enqueueMessage($db->getErrorMsg(),'error');
		}
		
		// Clear data of table temp
		$sql = "TRUNCATE TABLE #__obhelpdesk3_temp1";
		$db->setQuery($sql);
		$db->query();
		if($db->getErrorNum()){
			$app->enqueueMessage($db->getErrorMsg(),'error');
		}
		// update 
		$sql = "INSERT INTO #__obhelpdesk3_temp1(tid, first_msg_id, last_msg_id) 
				SELECT `tid`, min(`id`) as `first_msg_id`, max(`id`) as `last_msg_id` 
				FROM `#__obhelpdesk3_messages` AS `sm` GROUP BY `sm`.`tid`";
		$db->setQuery($sql);
		$db->query();
		if($db->getErrorNum()){
			$app->enqueueMessage($db->getErrorMsg(),'error');
		}
		// Count replies
		$sql = "CREATE TABLE `#__obhelpdesk3_temp2`(`tid` int,`count` smallint )";
		$db->setQuery($sql);
		$db->query();
		$sql = "TRUNCATE TABLE `#__obhelpdesk3_temp2`";
		$db->setQuery($sql);
		$db->query();
		$sql = "INSERT INTO `#__obhelpdesk3_temp2`(`tid`,`count`)
				SELECT m.tid, count(*) 
				FROM `#__obhelpdesk3_messages` AS m GROUP BY m.tid";
		$db->setQuery($sql);
		$db->query();
		
		# IMPORT TICKET
		$sql = "INSERT INTO `#__obhelpdesk3_tickets`
				(
					`id`,
					`staff`,
					`customer_id`,
					`customer_email`,
					`customer_fullname`,
					`departmentid`,
					`quickcode`,
					`subject`,
					`status`,
					`priority`,
					`created`,
					`updated`,
					`auto_close_sent`,
					`first_msg_id`,
					`last_msg_id`,
					`info`,
					`replies`
				)
				SELECT
					`t`.`id`,
					`t`.`user_id` AS `staff`,
					`t`.`customer_id`,
					`u`.`email` AS `customer_email`,
					`u`.`name` AS `customer_fullname`,
					`t`.`department_id` AS `departmentid`,
					`t`.`code` AS `quickcode`,
					`t`.`subject`,
					`t`.`status`,
					`t`.`priority`,
					`t`.`created`,
					`t`.`updated`,
					`t`.`auto_close_sent`,
					`tb`.`first_msg_id`,
					`tb`.`last_msg_id`,
					`t`.`info`,
					`tb2`.`count`
				FROM `#__obhelpdesk_tickets` AS t 
					INNER JOIN `#__users` AS u ON t.`customer_id`= u.`id`
					LEFT JOIN `#__obhelpdesk3_temp1` AS `tb` ON  t.id = tb.tid
					LEFT JOIN `#__obhelpdesk3_temp2` AS `tb2` ON t.id = tb2.tid
				";
		$db->setQuery($sql);
		$db->query();
		
		$sql = "DROP TABLE `#__obhelpdesk3_temp1`";
		$db->setQuery($sql);
		$db->query();
		
		$sql = "DROP TABLE `#__obhelpdesk3_temp2`";
		$db->setQuery($sql);
		$db->query();
	}


	/* doesn't use */
	function import_obhd_priorities() {
		$db = JFactory::getDbo();
		
		$pr_id_new = array(); // priority array new
		$gr_id_new = array(); // groups array new
		$staff_id_new = array(); // staff array new
		$cf_new = array(); // fields array new
		$de_id_new = array(); // department array new
		$ticket_id_new = array(); // ticket array new
		$msg_id_new = array(); // message array new
		
		// get priorities
		$query = "SELECT * FROM `#__obhelpdesk3_priority`";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		// import priorities
		foreach ($rows as $row) {
			$value = "('".$row->label."', '".$row->alias."', 1, '#".$row->color."', '".$row->ordering."', '".$row->checked_out."')";
			$query = "INSERT INTO `#__obhelpdesk3_priority`(`title`, `alias`, `published`, `color`, `ordering`, `checked_out`) VALUES ".$value;
			$db->setQuery($query);
			if($db->query()) {
				$pr_id_new[$row->id] = $db->insertid();
			}
		}
		
		// get groups
		$query = "SELECT * FROM `#__obhelpdesk3_groups`";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		// import groups
		foreach ($rows as $row) {
			$value = "(
			'".$row->name."', 1,
			'".$row->add_ticket."',
			'".$row->add_ticket_staff."',
			'".$row->add_ticket_users."',
			'".$row->update_ticket."',
			'".$row->delete_ticket."',
			'".$row->answer_ticket."',
			'".$row->delete_ticket_replies."',
			'".$row->update_ticket_replies."',
			'".$row->assign_tickets."',
			'".$row->change_ticket_status."',
			'".$row->see_other_tickets."',
			'".$row->see_unallocated_tickets."',
			'".$row->move_ticket."',
			'".$row->reopen_ticket."',
			'".$row->add_monitoring."',
			'".$row->checked_out."',
			'".$row->checked_out_time."'
			)";
				
			$query = "INSERT INTO `#__obhelpdesk3_groups`(
			`title`,
			`published`,
			`add_ticket`,
			`add_ticket_staffs`,
			`add_ticket_users`,
			`update_ticket`,
			`delete_ticket`,
			`answer_ticket`,
			`delete_ticket_replies`,
			`update_ticket_replies`,
			`assign_tickets`,
			`change_ticket_status`,
			`see_other_tickets`,
			`see_unassigned_tickets`,
			`move_ticket`,
			`reopen_ticket`,
			`add_monitoring`,
			`checked_out`,
			`checked_out_time`
			) VALUES ".$value;
			$db->setQuery($query);
			if($db->query()) {
				$gr_id_new[$row->id] = $db->insertid();
			}
		}
		
		/* Staffs */
		$query = "SELECT * FROM `#__obhelpdesk3_staff`";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ($rows as $row) {
			$query = " INSERT INTO `#__obhelpdesk3_staffs`"
			." SET `group_id` =".$gr_id_new[$row->group_id].", "
			." `user_id` =".$row->user_id.", "
			." `checked_out` =".$row->checked_out.", "
			." `checked_out_time` ='".$row->checked_out_time."'"
			;
			$db->setQuery($query);
			if($db->query()){
				$staff_id_new[$row->id] = $db->insertid();
			}
		}

		// CUSTOM FIELDS
		$query = "SELECT * FROM `#__obhelpdesk3_custom_fields`";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		// import custom fields
		foreach ($rows as $row) {
			$value = "(
				'".$row->name."', 1,
				'".$row->label."',
				'".$row->type."',
				'".$row->values."',
				'".$row->required."',
				'".$row->ordering."',
				'".$row->checked_out."',
				'".$row->checked_out_time."'
			)";

			$query = "INSERT INTO `#__obhelpdesk3_fields`(
			`name`,
			`published`,
			`title`,
			`type`,
			`values`,
			`required`,
			`ordering`,
			`checked_out`,
			`checked_out_time`
			) VALUES ".$value;
			$db->setQuery($query);
			if( $db->query() ) {
				$cf_new[$row->id] = $db->insertid();
			}
		}

		/* Departments */
		$query = "SELECT * FROM `#__obhelpdesk3_departments`";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ($rows as $row) {
			$query = "SELECT `customfield_id` FROM `#__obhelpdesk3_customfield_department` WHERE `department_id`=".$row->id;
			$db->setQuery($query);
			$fields = $db->loadColumn();
			$new_fields = array();
			foreach ($fields as $f) {
				$new_fields[] = $cf_new[$f];
			}
			$query = " INSERT INTO `#__obhelpdesk3_departments`"
			." SET `title` ='".$row->name."' ,"
			." `description`='".$row->description."' ,"
			." `prefix` ='".$row->prefix."' ,"
			." `assignment_type` ='".$row->assignment_type."' ,"
			." `generation_rule` ='".$row->generation_rule."' ,"
			." `next_ticket_number` ='".$row->next_ticket_number."' ,"
			." `user_email_ticket` ='".$row->user_email_ticket."' ,"
			." `staff_email_ticket` ='".$row->staff_email_ticket."' ,"
			." `file_upload` ='".$row->file_upload."' ,"
			." `notify_new_ticket_emails` ='".$row->notify_new_ticket_emails."' ,"
			." `notify_assign` ='".$row->notify_assign."' ,"
			." `file_upload_extensions` ='".$row->file_upload_extensions."' ,"
			." `priority` ='".$row->priority."' ,"
			." `ordering` ='".$row->ordering."', "
			." `usergroups` = 1,"
			." `fields` ='".implode(',', $new_fields)."' "
			;
			$db->setQuery($query);
			if($db->query()){
				$de_id_new[$row->id] = $db->insertid();
			}
		}

		/* staff_department */
		$query = "SELECT * FROM `#__obhelpdesk3_staff_to_department`";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach($rows as $row) {
			$query = " INSERT INTO `#__obhelpdesk3_staff_department`"
			." SET `user_id` =".$row->user_id." ,"
			." `department_id` =".$de_id_new[$row->department_id]." ,"
			." `open_tickets` =".$row->open_tickets." "
			;
			$db->setQuery($query);
			$db->query();
		}

	}
}
