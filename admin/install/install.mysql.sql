CREATE TABLE IF NOT EXISTS `#__obhelpdesk3_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__obhelpdesk3_customer_care` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `staff_id_list` text,
  `notify_email` text,
  `published` tinyint(1) DEFAULT NULL,
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

CREATE TABLE IF NOT EXISTS `#__obhelpdesk3_departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `kb_catid` varchar(255),
  `prefix` varchar(255) NOT NULL,
  `external_link` varchar(255) NOT NULL,
  `assignment_type` enum('static','automatic') NOT NULL,
  `generation_rule` enum('sequential','random') NOT NULL,
  `next_ticket_number` int(11) NOT NULL DEFAULT '1',
  `user_email_ticket` enum('0','1') NOT NULL,
  `staff_email_ticket` enum('0','1') NOT NULL,
  `file_upload` enum('yes','users','no') NOT NULL DEFAULT 'yes',
  `notify_new_ticket_emails` text NOT NULL,
  `notify_assign` enum('0','1') NOT NULL,
  `file_upload_extensions` text NOT NULL,
  `priority` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` timestamp NULL DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `usergroups` text NOT NULL,
  `fields` text,
  `label_color` varchar(255) NULL,
  `language` char(7) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

CREATE TABLE IF NOT EXISTS `#__obhelpdesk3_emailtemplates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `edit` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

CREATE TABLE IF NOT EXISTS `#__obhelpdesk3_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `helptext` text,
  `default_value` varchar(255),
  `values` text NOT NULL,
  `multiple` tinyint(4),
  `breakline` tinyint(1) NOT NULL DEFAULT '1',
  `size` int(3) NOT NULL DEFAULT '20',
  `rows` int(3) NOT NULL DEFAULT '60',
  `cols` int(3) NOT NULL DEFAULT '10',
  `editor` tinyint(1) NOT NULL DEFAULT '1',
  `required` enum('0','1') NOT NULL,
  `ordering` int(11) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

CREATE TABLE IF NOT EXISTS `#__obhelpdesk3_field_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `value` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
)  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__obhelpdesk3_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `add_ticket` enum('0','1') NOT NULL,
  `add_ticket_staffs` enum('0','1') NOT NULL,
  `add_ticket_users` enum('0','1') NOT NULL,
  `update_ticket` enum('0','1') NOT NULL,
  `delete_ticket` enum('0','1') NOT NULL,
  `answer_ticket` enum('0','1') NOT NULL,
  `delete_ticket_replies` enum('0','1') NOT NULL,
  `update_ticket_replies` enum('0','1') NOT NULL,
  `assign_tickets` enum('0','1') NOT NULL,
  `change_ticket_status` enum('0','1') NOT NULL,
  `see_other_tickets` enum('0','1') NOT NULL,
  `see_unassigned_tickets` enum('0','1') NOT NULL,
  `move_ticket` enum('0','1') NOT NULL,
  `reopen_ticket` enum('0','1') NOT NULL,
  `add_monitoring` enum('0','1') NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

CREATE TABLE IF NOT EXISTS `#__obhelpdesk3_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `reply_time` datetime NOT NULL,
  `files` text NOT NULL,
  PRIMARY KEY (`id`)
)  DEFAULT CHARSET=utf8 AUTO_INCREMENT=62 ;

CREATE TABLE IF NOT EXISTS `#__obhelpdesk3_priority` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#000000',
  `ordering` int(11) NOT NULL,
  `checked_out` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

CREATE TABLE IF NOT EXISTS `#__obhelpdesk3_replytemplates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `staff_id` int(11) NOT NULL,
  `enable` tinyint(1) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `default` tinyint(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  `modified_date` datetime NOT NULL,
  `created_date` datetime NOT NULL,
  `copy_from` int(11) NOT NULL,
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` timestamp NULL DEFAULT NULL,
  `hits` INT(11) ZEROFILL UNSIGNED NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

CREATE TABLE IF NOT EXISTS `#__obhelpdesk3_staffs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

CREATE TABLE IF NOT EXISTS `#__obhelpdesk3_staff_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `open_tickets` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

CREATE TABLE IF NOT EXISTS `#__obhelpdesk3_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_fullname` varchar(255) NOT NULL,
  `departmentid` int(11) NOT NULL,
  `quickcode` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `status` enum('open','on-hold', 'closed') NOT NULL,
  `priority` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `auto_close_sent` int(11) NOT NULL,
  `first_msg_id` int(11) NOT NULL,
  `last_msg_id` int(11) NOT NULL,
  `info` text NOT NULL,
  `replies` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT IGNORE INTO `#__obhelpdesk3_departments` (`id`, `title`, `description`, `prefix`, `assignment_type`, `generation_rule`, `next_ticket_number`, `user_email_ticket`, `staff_email_ticket`, `file_upload`, `notify_new_ticket_emails`, `notify_assign`, `file_upload_extensions`, `priority`, `ordering`, `checked_out`, `checked_out_time`, `published`, `usergroups`, `fields`) VALUES
(1, 'Presale', 'Presale issues', 'PRE', 'static', 'sequential', 1, '1', '1', 'yes', 'test', '1', 'zip', '8', 3, 0, '0000-00-00 00:00:00', 1, '1,3', '10,1,2,8,4,5,9,3,7,6'),
(2, 'Bill', 'Bill issues', 'BIL', 'static', '', 1, '1', '1', 'yes', '', '1', 'zip, jpg', '2', 1, 350, '0000-00-00 00:00:00', 1, '1,2', '2,4,5,3,7,6'),
(3, 'Technical', 'Technical issues', 'TCH', 'static', 'sequential', 1, '1', '1', 'yes', '', '1', 'zip', '1', 2, 0, '0000-00-00 00:00:00', 1, '1', '5');

INSERT IGNORE INTO `#__obhelpdesk3_emailtemplates` (`id`, `type`, `subject`, `message`, `edit`) VALUES
(1,'add_ticket_customer','[{ticket_code}] {ticket_subject}','<div style=\"margin-bottom: 4px; background-color: #008eb9; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px;\">\r\n<h2 style=\"padding: 10px 10px 10px 20px;\"><a href=\"{site_url}\" style=\"color: white; text-decoration: none;\">{site_name}</a></h2>\r\n</div>\r\n<div style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px; margin: 14px; position: relative;\">\r\n<div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"color: #222222;\">Hi </span><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 15.796875px;\">{customer_name}</span><span style=\"color: #222222;\">,</span></div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"color: #222222; font-size: small;\">You have a new ticket from {message_fromname}</span></div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 15.796875px;\">Subject: </span><strong>[{ticket_code}] {ticket_subject}</strong></div>\r\n</div>\r\n<div style=\"background: none repeat scroll 0% 0% #ffffdd; border: 1px solid #eeeecc; margin: 10px 0pt 15px; padding: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; background-color: #ffffff;\">{message_body}</span></div>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\">Other information:</p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 11.818181991577148px;\">{custom_fields}</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 11.818181991577148px;\"> </span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><a href=\"{ticket_url}\" target=\"_blank\" style=\"background-color: #008eb9; padding: 4px 12px; border: 1px outset #20aed9; font-family: \'Helvetica Neue\',Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bold; white-space: nowrap; color: #ffffff; text-shadow: 0pt -1px 0pt #3399dd; text-decoration: none;\">View this ticket</a></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"color: #222222; line-height: 18px;\"> </span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-size: small; line-height: 1.3em;\">Sincerely,</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-size: small; line-height: 1.3em;\">obExtensions for Joomla Customer Service</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><a href=\"{site_url}\"><span style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: small;\"><span style=\"line-height: 16.890625px;\">{site_url}</span></span></a></p>\r\n</div>',1),
(2,'add_ticket_staff','[{ticket_code}] {ticket_subject}','<div style=\"margin-bottom: 4px; background-color: #008eb9; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px;\">\r\n<h2 style=\"padding: 10px 10px 10px 20px;\"><a href=\"{site_url}\" style=\"color: white; text-decoration: none;\">{site_name}</a></h2>\r\n</div>\r\n<div style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px; margin: 14px; position: relative;\">\r\n<div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"color: #222222;\">Hi </span><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 15.796875px;\">{staff_name}</span><span style=\"color: #222222;\">,</span></div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"color: #222222; font-size: small;\">You have a new ticket from {message_fromname}</span></div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 15.796875px;\">Subject: </span><strong>[{ticket_code}] {ticket_subject}</strong></div>\r\n</div>\r\n<div style=\"background: none repeat scroll 0% 0% #ffffdd; border: 1px solid #eeeecc; margin: 10px 0pt 15px; padding: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; background-color: #ffffff;\">{message_body}</span></div>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\">Other information:</p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 11.818181991577148px;\">{custom_fields}</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 11.818181991577148px;\"> </span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><a href=\"{ticket_url}\" target=\"_blank\" style=\"background-color: #008eb9; padding: 4px 12px; border: 1px outset #20aed9; font-family: \'Helvetica Neue\',Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bold; white-space: nowrap; color: #ffffff; text-shadow: 0pt -1px 0pt #3399dd; text-decoration: none;\">View this ticket</a></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"color: #222222; line-height: 18px;\">Be sure to response to our valued customer within </span><span style=\"line-height: 18px; color: red;\"><strong>{overdue_time}-hour</strong></span><span style=\"color: #222222; line-height: 18px;\">.</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"color: #222222; line-height: 18px;\"> </span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-size: small; line-height: 1.3em;\">Sincerely,</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-size: small; line-height: 1.3em;\">obExtensions for Joomla Customer Service</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><a href=\"{site_url}\"><span style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: small;\"><span style=\"line-height: 16.890625px;\">{site_url}</span></span></a></p>\r\n</div>',1),
(3,'add_ticket_reply_customer','RE: [{ticket_code}] {ticket_subject}','<p> </p>\r\n<div style=\"margin-bottom: 4px; background-color: #008eb9; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px;\">\r\n<h2 style=\"padding: 10px 10px 10px 20px;\"><a href=\"{site_url}\" style=\"color: white; text-decoration: none;\">{site_name}</a></h2>\r\n</div>\r\n<div style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px; margin: 14px; position: relative;\">\r\n<div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"color: #222222;\">Hi </span><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 15.796875px;\">{customer_name}</span><span style=\"color: #222222;\">,</span></div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"color: #222222; font-size: small;\">You have a new message from {message_fromname}</span></div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 15.796875px;\">Re: [{ticket_code}] {ticket_subject}</span></div>\r\n</div>\r\n<div style=\"background: none repeat scroll 0% 0% #ffffdd; border: 1px solid #eeeecc; margin: 10px 0pt 15px; padding: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; background-color: #ffffff;\">{message_body}</span></div>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\">Other information:</p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 11.818181991577148px;\">{custom_fields}</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 11.818181991577148px;\"> </span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><a href=\"{ticket_url}\" target=\"_blank\" style=\"background-color: #008eb9; padding: 4px 12px; border: 1px outset #20aed9; font-family: \'Helvetica Neue\',Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bold; white-space: nowrap; color: #ffffff; text-shadow: 0pt -1px 0pt #3399dd; text-decoration: none;\">View this ticket</a></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"color: #222222; line-height: 18px;\"> </span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"color: #222222; line-height: 18px;\"><span>One of our staffs will response to you within </span><span style=\"line-height: 18px; color: red;\"><strong>{overdue_time}-hour</strong></span><span>.</span></span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-size: small; line-height: 1.3em;\">Sincerely,</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-size: small; line-height: 1.3em;\">obExtensions for Joomla Customer Service</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><a href=\"{site_url}\"><span style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: small;\"><span style=\"line-height: 16.890625px;\">{site_url}</span></span></a></p>\r\n</div>',1),
(4,'add_ticket_reply_staff','[{ticket_code}] {ticket_subject}','<div style=\"margin-bottom: 4px; background-color: #008eb9; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px;\">\r\n<h2 style=\"padding: 10px 10px 10px 20px;\"><a href=\"{site_url}\" style=\"color: white; text-decoration: none;\">{site_name}</a></h2>\r\n</div>\r\n<div style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px; margin: 14px; position: relative;\">\r\n<div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"color: #222222;\">Hi </span><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 15.796875px;\">{staff_name}</span><span style=\"color: #222222;\">,</span></div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"color: #222222; font-size: small;\">You have a new message from {message_fromname}</span></div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 15.796875px;\">Re: [{ticket_code}] {ticket_subject}</span></div>\r\n</div>\r\n<div style=\"background: none repeat scroll 0% 0% #ffffdd; border: 1px solid #eeeecc; margin: 10px 0pt 15px; padding: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; background-color: #ffffff;\">{message_body}</span></div>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\">Other information:</p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 11.818181991577148px;\">{custom_fields}</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><a href=\"{ticket_url}\" target=\"_blank\" style=\"background-color: #008eb9; padding: 4px 12px; border: 1px outset #20aed9; font-family: \'Helvetica Neue\',Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bold; white-space: nowrap; color: #ffffff; text-shadow: 0pt -1px 0pt #3399dd; text-decoration: none;\">View this ticket</a></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"color: #222222; line-height: 18px;\">Be sure to response to our valued customer within </span><span style=\"line-height: 18px; color: red;\"><strong>{overdue_time}-hour</strong></span><span style=\"color: #222222; line-height: 18px;\">.</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-size: small; line-height: 1.3em;\">Sincerely,</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-size: small; line-height: 1.3em;\">obExtensions for Joomla Customer Service</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><a href=\"{site_url}\"><span style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: small;\"><span style=\"line-height: 16.890625px;\">{site_url}</span></span></a></p>\r\n</div>',1),
(5,'notification_email','Your ticket will be closed','<p>Your ticket with subject \"{subject}\" had no activity for {inactive_interval} days.</p>\r\n<p>It will be automatically closed in {close_interval} days if no additional action is performed.</p>\r\n<p>Please log in to <br /><br /> <a href=\"{live_site}index.php?option=com_rsticketspro\">Our Support Center</a> <br /><br /> and go to <a href=\"{live_site}index.php?option=com_rsticketspro\">My Tickets</a> in order to view the status of your support request.</p>',0),
(6,'reject_email','Re: {subject}','<p>Hello {customer_name},<br /><br />Unfortunately your email for department {department} could not be processed. Only registered users can submit tickets by email.<br />We are sorry for the inconvenience. You can visit <a href=\"{live_site}\">our website</a> instead.</p>',0),
(7,'add_ticket_notify','[{ticket_code}] {ticket_subject}','<div style=\"margin-bottom: 4px; background-color: #008eb9; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px;\">\r\n<h2 style=\"padding: 10px 10px 10px 20px;\"><a href=\"{site_url}\" style=\"color: white; text-decoration: none;\">{site_name}</a></h2>\r\n</div>\r\n<div style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px; margin: 14px; position: relative;\">\r\n<div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"color: #222222;\">Hi</span><span style=\"color: #222222;\">,</span></div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"color: #222222; font-size: small;\">You have a new ticket from {message_fromname}</span></div>\r\n<div style=\"padding-bottom: 10px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 15.796875px;\">Subject: </span><strong>[{ticket_code}] {ticket_subject}</strong></div>\r\n</div>\r\n<div style=\"background: none repeat scroll 0% 0% #ffffdd; border: 1px solid #eeeecc; margin: 10px 0pt 15px; padding: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 12px; background-color: #ffffff;\">{message_body}</span></div>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\">Other information:</p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 11.818181991577148px;\">{custom_fields}</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-family: Tahoma, Helvetica, Arial, sans-serif; font-size: 11.818181991577148px;\"> </span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><a href=\"{ticket_url}\" target=\"_blank\" style=\"background-color: #008eb9; padding: 4px 12px; border: 1px outset #20aed9; font-family: \'Helvetica Neue\',Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bold; white-space: nowrap; color: #ffffff; text-shadow: 0pt -1px 0pt #3399dd; text-decoration: none;\">View this ticket</a></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"color: #222222; line-height: 18px;\"> </span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"color: #222222; line-height: 18px;\"><span>Be sure to response to our valued customer within </span><span style=\"line-height: 18px; color: red;\"><strong>{overdue_time}-hour</strong></span><span>.</span></span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-size: small; line-height: 1.3em;\">Sincerely,</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><span style=\"font-size: small; line-height: 1.3em;\">obExtensions for Joomla Customer Service</span></p>\r\n<p style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: 13px;\"><a href=\"{site_url}\"><span style=\"font-family: \'Helvetica Neue\', Arial, Helvetica, sans-serif; font-size: small;\"><span style=\"line-height: 16.890625px;\">{site_url}</span></span></a></p>\r\n</div>',1),
(8,'new_user_email','New user details','<p>Here are your login details:</p>\r\n<p>Username: <strong>{username}</strong></p>\r\n<p>Password: <strong>{password}</strong></p>\r\n<p>Please note that this is your temporary password. You can login and change it at any time.</p>\r\n<p> Please log in to <br/><br/>\r\n  <a href=\"{live_site}index.php?option=com_rsticketspro\">Our Support Center</a> <br/><br/>\r\n  and go to <a href=\"{live_site}\">My Tickets</a> in order to view the status of your support request.</p>',0);


INSERT IGNORE INTO `#__obhelpdesk3_fields` (`id`, `name`, `title`, `type`, `published`, `helptext`, `default_value`, `values`, `multiple`, `breakline`, `size`, `rows`, `cols`, `editor`, `required`, `ordering`, `checked_out`, `checked_out_time`) VALUES
(1, 'field_firstname', 'Firstname', 'text', 1, 'First Name of customer.', 'aaaaaaaaaaaa', 'aaa\r\nsdf\r\ndsf\r\nds\r\nf', 0, 1, 20, 30, 10, 1, '1', 1, 0, '0000-00-00 00:00:00'),
(2, 'products', 'Products', 'list', 1, 'Products', 'ObHelpDesk', ':-- Choose Products --\r\n1:ObRSS\r\n2:ObHelpDesk\r\n3:ObFoobla', 0, 1, 20, 30, 10, 1, '1', 2, 0, '0000-00-00 00:00:00'),
(3, 'sdfsdf Test', 'Test Radio', 'radio', 1, '', '', '1:Yes\r\n2:No', 0, 0, 20, 30, 10, 1, '1', 0, 0, '0000-00-00 00:00:00'),
(4, 'Test checkbox', 'Test Checkbox', 'checkboxes', 1, '', '', '1:Sport\r\n2:Film\r\n3:Travel', 0, 1, 20, 30, 10, 1, '1', 0, 0, '0000-00-00 00:00:00'),
(5, 'Test checkbox1', 'Test Checkbox1', 'checkbox', 1, '', '', '1:What do you like this field?', 0, 1, 20, 30, 10, 1, '0', 0, 0, '0000-00-00 00:00:00'),
(6, 'test textarea', 'Test Textarea', 'textarea', 1, 'sdfsdfdsf', 'cai gi the nay', '', 0, 0, 40, 10, 60, 1, '1', 0, 0, '0000-00-00 00:00:00'),
(7, 'Test Select Multiple', 'Test Select Multiple', 'list', 1, 'Test Select Multiple', '2|4', '0: Zero\r\n1: One\r\n2: Two\r\n3: Three\r\n4: Four ', 1, 1, 10, 10, 60, 1, '1', 0, 0, '0000-00-00 00:00:00'),
(8, 'test calendar', 'Test Calendar', 'calendar', 1, 'test calendar', '', '', 0, 1, 20, 10, 60, 1, '1', 0, 0, '0000-00-00 00:00:00'),
(9, 'test datetime', 'Test datetime', 'datetime', 1, 'test datetime', '', '', 0, 1, 20, 10, 60, 1, '1', 0, 0, '0000-00-00 00:00:00');

INSERT IGNORE INTO `#__obhelpdesk3_groups` (`id`, `title`, `published`, `add_ticket`, `add_ticket_staffs`, `add_ticket_users`, `update_ticket`, `delete_ticket`, `answer_ticket`, `delete_ticket_replies`, `update_ticket_replies`, `assign_tickets`, `change_ticket_status`, `see_other_tickets`, `move_ticket`, `reopen_ticket`, `add_monitoring`, `checked_out`, `checked_out_time`) VALUES
(1, 'Staff', 1, '1', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 0, '0000-00-00 00:00:00'),
(2, 'Super Staff', 1, '1', '1', '1', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', 350, '0000-00-00 00:00:00');

INSERT IGNORE INTO `#__obhelpdesk3_priority` (`id`, `title`, `alias`, `published`, `color`, `ordering`, `checked_out`) VALUES
(1, 'High', 'high', 1, '#e60e32', 4, 0),
(2, 'Normal', 'normal', 1, '#0000FF', 2, 0),
(3, 'Low', 'low', 1, '#000000', 1, 0);

INSERT IGNORE INTO `#__obhelpdesk3_staffs`
(	`id`,
	`group_id`,
	`user_id`,
	`checked_out`,
	`checked_out_time`)
	SELECT 
		'1', (SELECT id FROM #__obhelpdesk3_groups LIMIT 1) AS `group_id`,
		u.id AS `user_id`,
		'0' AS `checked_out`,
		'0000-00-00 00:00:00' AS `checked_out_time`
	FROM #__users AS u LEFT JOIN #__user_usergroup_map AS uum ON u.id = uum.user_id
	WHERE uum.group_id=8 ORDER BY u.lastvisitDate DESC LIMIT 1;

INSERT IGNORE INTO `#__obhelpdesk3_replytemplates` (`id`, `subject`, `content`, `staff_id`, `enable`, `published`, `default`, `ordering`, `modified_date`, `created_date`, `copy_from`, `checked_out`, `checked_out_time`) VALUES
(1, 'Test', '<p>{username} Show staff name</p>\r\n<p>{customer} Show customer name</p>\r\n<p>{date} Show current date</p>\r\n<p>{cursor} Set cursor to here when start to reply</p>', (SELECT `user_id` FROM `#__obhelpdesk3_staffs` LIMIT 1), 0, 1, 0, 2, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00'),
(2, 'Testsss', '<p>ssdfsdfsdf</p>', (SELECT `user_id` FROM `#__obhelpdesk3_staffs` LIMIT 1), 0, 1, 0, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00');

INSERT IGNORE INTO `#__obhelpdesk3_staff_department`
(
`id`,
`user_id`,
`department_id`,
`open_tickets`)
	SELECT 
		1 AS `id`,
		(SELECT `user_id` FROM `#__obhelpdesk3_staffs` LIMIT 1) AS `user_id`
		, d.id AS `department_id`
		, 0 AS `open_tickets`
	FROM #__obhelpdesk3_departments AS d;