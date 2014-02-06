<?php
defined( '_JEXEC' ) or die;
define( 'DEFAULT_USER_GROUP', 1 );
jimport( 'joomla.filesystem.file' );

/**
 * @subpackage    com_obhelpdesk
 */
class obHelpDeskTicketHelper {
	/**
	 * Return a ticket object
	 * @param $tid
	 *
	 * @return mixed
	 */
	public static function load( $tid ) {
		$db    = JFactory::getDbo();
		$query = "SELECT * FROM `#__obhelpdesk3_tickets` WHERE `id` = " . $tid;
		$db->setQuery( $query );

		return $db->loadObject();
	}

	/**
	 * Check if the department ID is existing
	 * @param $did
	 *
	 * @return bool
	 */
	public static function isDepartment( $did ) {
		$db    = JFactory::getDbo();
		$query = "SELECT COUNT(*) FROM `#__obhelpdesk3_departments` WHERE id=" . $did . " AND published = 1";
		$db->setQuery( $query );
		if ( $db->loadResult() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a department is external type, return false if it is a regular department
	 * Return $external_link if it is an external department
	 * @param $did
	 */
	public static function isExternalDepartment( $did ) {
		$db    = JFactory::getDbo();
		$query = "SELECT `external_link` FROM `#__obhelpdesk3_departments` WHERE id=" . $did . "";
		$db->setQuery( $query );
		if ( $external_link = $db->loadResult() ) {
			return $external_link;
		}

		return false;
	}

	public static function getDepartmentList( $userid ) {
		$db   = JFactory::getDbo();
		$user = JFactory::getUser( $userid );
		$re   = array();

		$query = "SELECT * FROM `#__obhelpdesk3_departments` WHERE `published` = 1 ORDER BY `ordering`";
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		if ( count( $rows ) ) {
			foreach ( $rows as $row ) {
				$groups = explode( ',', $row->usergroups );
				if ( in_array( DEFAULT_USER_GROUP, $groups ) or count( array_intersect( $user->groups, $groups ) ) ) {
					$re[] = $row;
				}
			}
		}

		return $re;
	}

	public static function getDepartment( $did ) {
		$db    = JFactory::getDbo();
		$query = "SELECT * FROM `#__obhelpdesk3_departments` WHERE `published` = 1 AND id = " . $did;
		$db->setQuery( $query );

		return $db->loadObject();
	}

	public static function getFields( $did, $tid = 0 ) {
		$db    = JFactory::getDbo();
		$query = "SELECT `fields` FROM `#__obhelpdesk3_departments` WHERE `published` = 1 AND id = " . $did;
		$db->setQuery( $query );
		$str_fields = $db->loadResult();

		$fields = array();
		if ( $str_fields ) {
			$fields = explode( ',', $str_fields );
		}
		if ( ! count( $fields ) ) {
			return;
		}
		$sql = "SELECT * FROM `#__obhelpdesk3_fields` WHERE id IN(" . $str_fields . ") AND published = 1 ORDER BY `ordering`";
		$db->setQuery( $sql );
		$re = $db->loadObjectList();
		if ( $tid && $re ) {
			for ( $i = 0; $i < count( $re ); $i ++ ) {
				$query = "SELECT DISTINCT `value` FROM `#__obhelpdesk3_field_values` WHERE `ticket_id`=" . $tid . " AND `field_id` = " . $re[$i]->id;
				$db->setQuery( $query );
				$cols = $db->loadColumn();
				if ( count( $cols ) ) {
					$re[$i]->default_value = implode( '|', $cols );
				}
			}
		}

		return $re;
	}

	public static function getAdditionInfo( $logged ) {
		$ticketinfo['user-agent'] = '<strong>{agent}</strong>: ' . $_SERVER['HTTP_USER_AGENT'];
		$ticketinfo['referer']    = '<strong>{referer}</strong>: ' . $_SERVER['HTTP_REFERER'];
		$ticketinfo['ip']         = '<strong>{ip}</strong>: ' . $_SERVER['REMOTE_ADDR'];
		$ticketinfo['logged']     = '<strong>{logged}</strong>: ' . ( $logged ? '{yes}' : '{no}' );

		return implode( $ticketinfo, '<br />' );
	}

	public static function UploadFiles( $files, $files_tmp ) {
		$filepath = JPATH_COMPONENT . DS . 'uploads';
// 		$str_time = JFactory::getDate()->toFormat('%Y%m%d%H%M%S');
		$str_time   = JFactory::getDate()->format( 'YmdHis' );
		$attachment = array();
		if ( count( $files_tmp ) ) {
			for ( $i = 0; $i < count( $files ); $i ++ ) {
				if ( $files[$i] ) {
					$f_tmp = $files_tmp[$i];
					$f     = $str_time . $files[$i];
					if ( ! JFile::exists( $filepath . DS . $f ) ) {
						JFile::upload( $f_tmp, $filepath . DS . $f );
						$attachment[] = $filepath . DS . $f;
					}
				}
			}
		}

		return $attachment;
	}

	/**
	 * Check if a ticket is overdue or not
	 * return true if the ticket is overdue, otherwise it returns false
	 */
	public static function isOverdueTicket( $ticket_id ) {
		# get overduetime
		$overduetime = obHelpDeskHelper::getConfig( 'overduetime' )->value;
		$db          = JFactory::getDbo();

		# get last message time
		$query = '
			SELECT max(m.`reply_time`) as last_message, m.`email`, m.`tid`
			FROM `#__obhelpdesk3_messages` AS m
			INNER JOIN `#__obhelpdesk3_tickets` AS t
			ON m.`tid` = t.`id` WHERE m.`tid` = ' . $ticket_id . ' AND t.`status` = \'open\'
			GROUP BY m.`tid`';
		$db->setQuery( $query );
		$result = $db->loadObject();
		if ( ! $result || ! $result->last_message ) {
			return false;
		} else {
			$current_time = time();
			#get timezone by second
			$timezone   = date( 'Z', time() );
			$reply_time = strtotime( $result->last_message );
			if ( ( $current_time - $reply_time - $timezone ) >= $overduetime ) { // pass overduetime => return true
				return true;
			} else { // return false
				return false;
			}
		}
	}

	public static function assignUserTicket( &$ticket, $user, $data, $assignment_type ) {
		$app     = JFactory::getApplication();
		$session = JFactory::getSession();
		// If not loggin
		if ( ! $user->id ) {
			/*
			// check and insert email to customers table.
			$password = obHelpDeskUserHelper::generatePassword();
			if(!obHelpDeskUserHelper::NewUserProcess($ticket->customer_email, $ticket->customer_fullname, $password)) {
				// Email is exist on system 
				// If not enter code
				if(!$session->get('obhelpdesk_logged')) {
					// ==> redirect to enter code.
					$msg = JText::_('Email is exists!, please enter code or submit by other email');
					$app->enqueueMessage($msg, 'error');
					return false;
				} elseif($session->get('obhelpdesk_email') ==  trim($data['email'])) {
					$ticket->customer_email = $data['email'];
					$ticket->customer_fullname = $data['fullname'];
				} else {
					// ==> redirect to enter code.
					$msg = JText::_('Email is exists!, please enter code or submit by other email');
					$app->enqueueMessage($msg, 'error');
					return false;
				}
				$password = '';
			} else {
				//insert email to customer table successfull.
				$ticket->customer_email = $data['email'];
				$ticket->customer_fullname = $data['fullname'];
			}
			*/
		} else {
			$ticket->customer_email    = $user->email;
			$ticket->customer_fullname = $user->name;
			$ticket->customer_id       = $user->id;
			if ( obHelpDeskUserHelper::is_staff( $user->id ) ) { // IF current user is a staff.
				// check staff have add_ticket_users permission.
				if ( obHelpDeskUserHelper::checkPermission( $user->id, 'add_ticket_users' ) or obHelpDeskUserHelper::checkPermission( $user->id, 'add_ticket_staffs' ) ) {
					// check user selected when submit ticket.
					if ( $data['user_id'] ) {
						$user_choose               = JFactory::getUser( $data['user_id'] );
						$ticket->customer_email    = $user_choose->email;
						$ticket->customer_fullname = $user_choose->name;
						$ticket->customer_id       = $user_choose->id;
					}
				}
				// If assign ticket automaticly
				if ( $assignment_type == 'automatic' ) {
					$ticket->staff = $user->id;
				}
			}
		}

		return true;
	}

	/** Get List priority
	 * $default: default value
	 */
	public static function getListPriority( $default, $disabled = false ) {
		$db    = JFactory::getDBO();
		$query = "SELECT * FROM `#__obhelpdesk3_priority` ORDER BY `ordering` ASC";
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		foreach ( $rows as $row ) {
			$obj        = new stdClass();
			$obj->value = $row->id;
			$obj->text  = $row->title;
			$arr_obj[]  = $obj;
		}
		$str_disabled = '';
		if ( $disabled ) {
			$str_disabled = ' disabled="true"';
		}

		$list = JHTML::_( 'select.genericlist', $arr_obj, 'jform[priority]', 'class="inputbox" style="width: 80px;" size="1"' . $str_disabled, 'value', 'text', $default );

		return $list;
	}

	public static function getListStatus( $default, $disabled = false ) {
		$status = array(
			'open'    => JText::_( 'COM_OBHELPDESK_STATUS_OPEN' ),
			'closed'  => JText::_( 'COM_OBHELPDESK_STATUS_CLOSED' ),
			'on-hold' => JText::_( 'COM_OBHELPDESK_STATUS_ONHOLD' )
		);
		foreach ( $status as $key => $value ) {
			$obj        = new stdClass();
			$obj->value = $key;
			$obj->text  = $value;
			$arr_obj[]  = $obj;
		}
		$str_disabled = '';
		if ( $disabled ) {
			$str_disabled = ' disabled="true"';
		}

		$list = JHTML::_( 'select.genericlist', $arr_obj, 'jform[status]', 'class="inputbox" style="width: 80px;" size="1"' . $str_disabled, 'value', 'text', $default );

		return $list;
	}

	public static function getReplyTemplate( $content, $customer_id = 0, $customer_email = '' ) {
		$db   = JFactory::getDBO();
		$user = JFactory::getUser();
// 		$editor = JFactory::getEditor();
		if ( ! $customer_id && ! $customer_email ) {
			return $content;
		}
		if ( $customer_id ) {
			$customer = JFactory::getUser( $customer_id );
		} else {
			$query = "SELECT `fullname` as name FROM `#__obhelpdesk3_customers` WHERE `email` = '" . $customer_email . "'";
			$db->setQuery( $query );
			$customer = $db->loadObject();
		}
		$modifieddate = JFactory::getDate( 'now' );
// 		$modifieddate = $modifieddate->toFormat('%Y-%m-%d');
		$modifieddate = $modifieddate->format( 'Y-m-d' );
		$content      = str_replace( '{date}', $modifieddate, $content );
		$content      = str_replace( '{customer}', $customer->name, $content );
		$content      = str_replace( '{username}', $user->name, $content );

		return $content;
	}

	public static function addTicketMessage( $ticket_id, $user, $content, $reply_time, $attachment = null, $params = array() ) {
		if ( ! $ticket_id || ! $user || ! $content ) {
			return false;
		}
		$db      = JFactory::getDbo();
		$app     = JFactory::getApplication();
		$isStaff = obHelpDeskUserHelper::is_staff( $user->id );

		// store message information

		$data               = array();
		$data['tid']        = $ticket_id;
		$data['user_id']    = $user->id;
		$data['email']      = $user->email;
		$data['content']    = $content;
		$data['reply_time'] = $reply_time;
		$data['files']      = $attachment;

		$message = JTable::getInstance( 'TicketMessage', 'obHelpDeskTable' );
		$message->bind( $data );
		if ( ! $message->store() ) {
			return false;
		}

		// store ticket information

		$ticket = JTable::getInstance( 'Ticket', 'obHelpDeskTable' );
		$ticket->load( $ticket_id );
		$replies             = self::countTicketMesssage( $ticket_id );
		$ticket->replies     = $replies;
		$ticket->last_msg_id = $message->id;
		$ticket->status      = ( $isStaff ) ? 'on-hold' : 'open';
		if ( ! $ticket->staff && $isStaff ) {
			$ticket->staff = $user->id;
		} else {
			$department = JTable::getInstance( 'Department', 'obHelpDeskTable' );
			$department->load( $ticket->departmentid );
			if ( $department->assignment_type == 'automatic' ) {
				$staff_id      = obHelpDeskHelper::getFreeStaff( $ticket->departmentid );
				$ticket->staff = $staff_id;
			}
		}
		$ticket->store();

		// send email notify

		$cuser_is_staff = ( $user->id && $isStaff );
		$arg            = array( 'message' => $message, 'department' => $department );
		if ( $cuser_is_staff ) {
			#TODO: send mail "new ticket reply" to customer
			if ( $department->user_email_ticket ) {
				obHelpDeskHelper::SendMail( 'add_ticket_reply_customer', $ticket->customer_email, $ticket->customer_fullname, $content, $ticket, $attachment, $arg );
			}

			if ( $user->id != $ticket->staff && $department->staff_email_ticket ) {
				$ticket_staff = JFactory::getUser( $ticket->staff );
				#TODO: send "new ticket reply" email to ticket staff
				obHelpDeskHelper::SendMail( 'add_ticket_reply_staff', $ticket_staff->email, $ticket_staff->name, $content, $ticket, $attachment, $arg );
			}

		} else {
			if ( $department->staff_email_ticket ) {
				$ticket_staff = JFactory::getUser( $ticket->staff );
				#TODO: send "new ticket reply" email to ticket staff
				obHelpDeskHelper::SendMail( 'add_ticket_reply_staff', $ticket_staff->email, $ticket_staff->name, $content, $ticket, $attachment, $arg );
			}
		}

		return $ticket;
	}

	public static function countTicketMesssage( $ticket_id ) {
		$db  = JFactory::getDbo();
		$app = JFactory::getApplication();
		$sql = 'SELECT count(*) FROM `#__obhelpdesk3_messages` WHERE `tid`=' . $ticket_id;
		$db->setQuery( $sql );
		$res = $db->loadResult();
		if ( $db->getErrorNum() ) {
			return false;
		}

		return $res;
	}

	public static function addTicket( $user, $did, $subject, $content, $reply_time, $files, $attachment, $params = null ) {
		$db         = JFactory::getDbo();
		$department = JTable::getInstance( 'Department', 'obHelpDeskTable' );
		$ticket     = JTable::getInstance( 'Ticket', 'obHelpDeskTable' );
		$message    = JTable::getInstance( 'TicketMessage', 'obHelpDeskTable' );

		$department->load( $did );

		$ticket->staff = 0;
		// Process Assign Ticket
		$assignment_type = $department->assignment_type;

		# auto assign
		if ( $department->assignment_type == 'automatic' ) {
			$staff_id      = obHelpDeskHelper::getFreeStaff( $department->id );
			$ticket->staff = $staff_id;
		}

		$ticket->customer_id       = $user->id;
		$ticket->customer_email    = $user->email;
		$ticket->customer_fullname = $user->name;
		$ticket->subject           = $subject;
		$ticket->priority          = 1;
		$ticket->departmentid      = $did;
		$ticket->status            = 'open';

		$time              = JFactory::getDate();
		$ticket->created   = $reply_time;
		$ticket->updated   = $reply_time;
		$ticket->quickcode = obHelpDeskHelper::generateQuickCode( $ticket->created );
		$ticket->info      = '';

		//store ticket's information.
		if ( ! $ticket->store() ) {
			return false;
		}
		// Upload file process.
// 		$attachment = obHelpDeskTicketHelper::UploadFiles($files, $files_tmp);
		// save content of ticket to message table.
		$message = JTable::getInstance( 'TicketMessage', 'obHelpDeskTable' );

		$message->tid        = $ticket->id;
		$message->user_id    = $user->id;
		$message->email      = $user->email;
		$message->content    = $content;
		$message->reply_time = $reply_time;
		$message->files      = $files;

		if ( ! $message->store() ) {
			return false;
		}

		/* Store last message ID to ticket table */
		$ticket->first_msg_id = $message->id;
		$ticket->last_msg_id  = $message->id;

		if ( ! $ticket->store() ) {
			return false;
		}

		$body = $content;
		$c    = 1;

		$arg = array( 'message' => $message, 'department' => $department );
		if ( trim( $department->notify_new_ticket_emails ) ) {
			# SEND EMAIL TO NOTIFY EMAILS
			$emails = explode( "\n", $department->notify_new_ticket_emails );
			if ( count( $emails ) ) {
				obHelpDeskHelper::SendMail( 'add_ticket_notify', $emails, '', $body, $ticket, $attachment, $arg );
			}
		}

		$sql = 'SELECT `staff_id_list`, `notify_email` FROM `#__obhelpdesk3_customer_care` WHERE `customer_id`=' . $user->id . ' AND `published`=1';
		$db->setQuery( $sql );
		$ccs = $db->loadObject();

		if ( $ccs ) {
			# SEND EMAIL TO STAFF
			$staffs = obHelpDeskHelper::getStaffList( $ccs->staff_id_list );
			if ( $staffs && count( $staffs ) ) {
				foreach ( $staffs as $staff ) {
					$arg['staff'] = $staff;
					obHelpDeskHelper::SendMail( 'add_ticket_staff', $staff->email, $staff->name, $body, $ticket, $attachment, $arg );
				}
			}
			# SEND EMAIL TO NOTIFY EMAILS
			$emails = explode( "\n", $ccs->notify_email );
			if ( $emails && is_array( $emails ) && count( $emails ) ) {
				obHelpDeskHelper::SendMail( 'add_ticket_notify', $emails, '', $body, $ticket, $attachment, $arg );
			}
		}


		# SEND EMAIL TO ALL STAFF
		$staff_ids = obHelpDeskHelper::getStaffIdsInDepartment( $department->id );
		if ( $staff_ids && count( $staff_ids ) ) {
			foreach ( $staff_ids as $staff_id ) {
				$staff        = JFactory::getUser( $staff_id );
				$arg['staff'] = $staff;
				obHelpDeskHelper::SendMail( 'add_ticket_staff', $staff->email, $staff->name, $body, $ticket, $attachment, $arg );
			}
		}

		return $ticket;
	}
}