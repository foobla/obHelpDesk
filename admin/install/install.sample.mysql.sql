#############################################
# SAMPLE DATA for obHelpDesk 3 Installation #
#############################################

###############
# DEPARTMENTS #
###############
INSERT IGNORE INTO `j25test_obhelpdesk3_departments` (`id`, `title`, `description`, `prefix`, `assignment_type`, `generation_rule`, `next_ticket_number`, `user_email_ticket`, `staff_email_ticket`, `file_upload`, `notify_new_ticket_emails`, `notify_assign`, `file_upload_extensions`, `priority`, `ordering`, `checked_out`, `checked_out_time`, `published`, `usergroups`, `fields`) VALUES
(1, 'Presale', 'Presale issues', 'PRE', 'static', 'sequential', 1, '1', '1', 'yes', 'test', '1', 'zip', '8', 3, 0, '0000-00-00 00:00:00', 1, '1,3', '10,1,2,8,4,5,9,3,7,6'),
(2, 'Bill', 'Bill issues', 'BIL', 'static', '', 1, '1', '1', 'yes', '', '1', 'zip, jpg', '2', 1, 350, '0000-00-00 00:00:00', 1, '1,2', '2,4,5,3,7,6'),
(3, 'Technical', 'Technical issues', 'TCH', 'static', 'sequential', 1, '1', '1', 'yes', '', '1', 'zip', '1', 2, 0, '0000-00-00 00:00:00', 1, '1', '5');

###############
# EMAILS      #
###############
INSERT IGNORE INTO `j25test_obhelpdesk3_emailtemplates` (`id`, `type`, `subject`, `message`, `edit`) VALUES
(1, 'add_ticket_customer', 'Confirm customer about new ticket submitted', '<p>Hello {customer_name},</p>\r\n<p>Thank you for contacting us. One of our staff members will attend to your problem as soon as possible.<br />You can view your ticket here:<br /><a href="{ticket}">{code}</a></p>', 0),
(2, 'add_ticket_staff', 'Notify staff about new ticket submitted', '<p>Hello,</p>\r\n<p>A new ticket requires your attention:</p>\r\n<p><a href="{ticket}">{code}</a></p>\r\n<p>{customer_email} wrote:</p>\r\n<p>{message}</p>\r\n<p>{custom_fields}</p>', 0),
(3, 'add_ticket_reply_customer', 'Notify customer about new response added', '<p>Hello {customer_name}.</p>\r\n<p>You have a new message from {staff_name}.<br />Re: {subject}<br />Message: {message}<br /><br />You can view your ticket here:<br /><a href="{ticket}">{code}</a></p>', 0),
(4, 'add_ticket_reply_staff', 'Notify staff about new response added', '<p>Hello {staff_name}.</p>\r\n<p>You have a new message from  {customer_name}.<br /> Re: {subject}<br /> Message: {message}<br /> <br /> You can view the ticket here:<br /> <a href="{ticket}">{code}</a></p>', 0),
(5, 'notification_email', 'Your ticket will be closed shortly', '<p>Your ticket with subject "{subject}" had no activity for {inactive_interval} days.</p>\r\n<p>It will be automatically closed in {close_interval} days if no additional action is performed.</p>\r\n<p>Please log in to <br /><br /> <a href="{live_site}index.php?option=com_rsticketspro">Our Support Center</a> <br /><br /> and go to <a href="{live_site}index.php?option=com_rsticketspro">My Tickets</a> in order to view the status of your support request.</p>', 0),
(6, 'reject_email', 'Re: {subject}', '<p>Hello {customer_name},<br /><br />Unfortunately your email for department {department} could not be processed. Only registered users can submit tickets by email.<br />We are sorry for the inconvenience. You can visit <a href="{live_site}">our website</a> instead.</p>', 0),
(7, 'add_ticket_notify', 'Re: {subject} ', '<p>Hello,</p>\r\n<p>A new ticket has been added:</p>\r\n<p><a href="{ticket}">{code}</a></p>\r\n<p>{customer_email} wrote:</p>\r\n<p>{message}</p>\r\n<p>{custom_fields}</p>', 0),
(8, 'new_user_email', 'New user details', '<p>Here are your login details:</p>\r\n<p>Username: <strong>{username}</strong></p>\r\n<p>Password: <strong>{password}</strong></p>\r\n<p>Please note that this is your temporary password. You can login and change it at any time.</p>\r\n<p> Please log in to <br/><br/>\r\n  <a href="{live_site}index.php?option=com_rsticketspro">Our Support Center</a> <br/><br/>\r\n  and go to <a href="{live_site}">My Tickets</a> in order to view the status of your support request.</p>', 0);

#################
# CUSTOM FIELDS #
#################
INSERT IGNORE INTO `j25test_obhelpdesk3_fields` (`id`, `name`, `title`, `type`, `published`, `helptext`, `default_value`, `values`, `multiple`, `breakline`, `size`, `rows`, `cols`, `editor`, `required`, `ordering`, `checked_out`, `checked_out_time`) VALUES
(1, 'field_firstname', 'Firstname', 'text', 1, 'First Name of customer.', 'aaaaaaaaaaaa', 'aaa\r\nsdf\r\ndsf\r\nds\r\nf', 0, 1, 20, 30, 10, 1, '1', 1, 0, '0000-00-00 00:00:00'),
(2, 'products', 'Products', 'list', 1, 'Products', 'ObHelpDesk', ':-- Choose Products --\r\n1:ObRSS\r\n2:ObHelpDesk\r\n3:ObFoobla', 0, 1, 20, 30, 10, 1, '1', 2, 0, '0000-00-00 00:00:00'),
(3, 'sdfsdf Test', 'Test Radio', 'radio', 1, '', '', '1:Yes\r\n2:No', 0, 0, 20, 30, 10, 1, '1', 0, 0, '0000-00-00 00:00:00'),
(4, 'Test checkbox', 'Test Checkbox', 'checkboxes', 1, '', '', '1:Sport\r\n2:Film\r\n3:Travel', 0, 1, 20, 30, 10, 1, '1', 0, 0, '0000-00-00 00:00:00'),
(5, 'Test checkbox1', 'Test Checkbox1', 'checkbox', 1, '', '', '1:What do you like this field?', 0, 1, 20, 30, 10, 1, '0', 0, 0, '0000-00-00 00:00:00'),
(6, 'test textarea', 'Test Textarea', 'textarea', 1, 'sdfsdfdsf', 'cai gi the nay', '', 0, 0, 40, 10, 60, 1, '1', 0, 0, '0000-00-00 00:00:00'),
(7, 'Test Select Multiple', 'Test Select Multiple', 'list', 1, 'Test Select Multiple', '2|4', '0: Zero\r\n1: One\r\n2: Two\r\n3: Three\r\n4: Four ', 1, 1, 10, 10, 60, 1, '1', 0, 0, '0000-00-00 00:00:00'),
(8, 'test calendar', 'Test Calendar', 'calendar', 1, 'test calendar', '', '', 0, 1, 20, 10, 60, 1, '1', 0, 0, '0000-00-00 00:00:00'),
(9, 'test datetime', 'Test datetime', 'datetime', 1, 'test datetime', '', '', 0, 1, 20, 10, 60, 1, '1', 0, 0, '0000-00-00 00:00:00');

##########
# GROUPS #
##########
INSERT IGNORE INTO `j25test_obhelpdesk3_groups` (`id`, `title`, `published`, `add_ticket`, `add_ticket_staffs`, `add_ticket_users`, `update_ticket`, `delete_ticket`, `answer_ticket`, `delete_ticket_replies`, `update_ticket_replies`, `assign_tickets`, `change_ticket_status`, `see_other_tickets`, `move_ticket`, `reopen_ticket`, `add_monitoring`, `checked_out`, `checked_out_time`) VALUES
(1, 'Staff', 1, '1', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 0, '0000-00-00 00:00:00'),
(2, 'Super Staff', 1, '1', '1', '1', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', 350, '0000-00-00 00:00:00');

###############
# PRIORITIES  #
###############
INSERT IGNORE INTO `j25test_obhelpdesk3_priority` (`id`, `title`, `alias`, `published`, `color`, `ordering`, `checked_out`) VALUES
(1, 'High', 'high', 1, '#e60e32', 4, 0),
(2, 'Normal', 'normal', 1, '#0000FF', 2, 0),
(3, 'Low', 'low', 1, '#000000', 1, 0);

#########
# STAFF #
#########
INSERT INTO `j25test_obhelpdesk3_staffs`
(	`group_id`,
	`user_id`,
	`checked_out`,
	`checked_out_time`)
	SELECT 
		(SELECT id FROM j25test_obhelpdesk3_groups LIMIT 1) AS `group_id`,
		u.id AS `user_id`,
		'0' AS `checked_out`,
		'0000-00-00 00:00:00' AS `checked_out_time`
	FROM j25test_users AS u LEFT JOIN j25test_user_usergroup_map AS uum ON u.id = uum.user_id
	WHERE uum.group_id=8 ORDER BY u.lastvisitDate DESC LIMIT 1;

####################
# CANNED RESPONSES #
####################
INSERT IGNORE INTO `j25test_obhelpdesk3_replytemplates` (`id`, `subject`, `content`, `staff_id`, `enable`, `published`, `default`, `ordering`, `modified_date`, `created_date`, `copy_from`, `checked_out`, `checked_out_time`) VALUES
(1, 'Test', '<p>{username} Show staff name</p>\r\n<p>{customer} Show customer name</p>\r\n<p>{date} Show current date</p>\r\n<p>{cursor} Set cursor to here when start to reply</p>', (SELECT `user_id` FROM `j25test_obhelpdesk3_staffs` LIMIT 1), 0, 1, 0, 2, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00'),
(2, 'Testsss', '<p>ssdfsdfsdf</p>', (SELECT `user_id` FROM `j25test_obhelpdesk3_staffs` LIMIT 1), 0, 1, 0, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00');

####################
# STAFF-DEPARTMENT #
####################
INSERT INTO `j25test_obhelpdesk3_staff_department`
(
`user_id`,
`department_id`,
`open_tickets`)
	SELECT 
		(SELECT `user_id` FROM `j25test_obhelpdesk3_staffs` LIMIT 1) AS `user_id`
		, d.id AS `department_id`
		, 0 AS `open_tickets`
	FROM j25test_obhelpdesk3_departments AS d;