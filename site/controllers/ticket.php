<?php

// No direct access.
defined( '_JEXEC' ) or die;

jimport( 'joomla.application.component.controllerform' );
JTable::addIncludePath( JPATH_COMPONENT . DS . 'tables' );

/**
 * Ticket controller class.
 * @since       1.6
 */
class obHelpDeskControllerTicket extends JControllerForm {
	function __construct( $default = array() ) {
		parent::__construct( $default );
		// here is where register tasks 
	}

	public function display( $cachable = false, $urlparams = false ) {
		parent::display( $cachable = false, $urlparams = false );
	}

	/**
	 * View Reply Form
	 */

	function viewdetail() {
		$app  = JFactory::getApplication();
		$view = $this->getView( 'ticket', 'reply' );
		$user = JFactory::getUser();
		// Get/Create the model
		if ( $model = $this->getModel() ) {
			// Push the model into the view (as default)
			$view->setModel( $model, true );
		}

		// Set the layout
		$view->setLayout( 'reply' );

		// Display the view
		$view->display();
	}

	function newticket() {
		$user    = JFactory::getUser();
		$app     = JFactory::getApplication();
		$db      = JFactory::getDbo();
		$session = JFactory::getSession();
		JSession::checkToken() or $app->redirect( 'index.php?option=com_obhelpdesk&view=tickets', 'Invalid Token', 'message' );
		$config               = JComponentHelper::getParams( 'com_obhelpdesk' );
		$submit_without_login = $config->get( 'submit_without_login' );
		if ( ! $submit_without_login && ! $user->id ) {
			return false;
		}
		$data = JRequest::getVar( 'jform', array(), 'post', 'array' );
		$session->set( 'obhelpdesk_data', $data );
		$password = '';
		$logged   = false;
		if ( $user->id ) {
			$logged = true;
		}

		// get files uploaded information.
		$files        = null;
		$files_tmp    = null;
		$files_upload = JRequest::getVar( 'FileName', null, 'files', 'array' );
		if ( $files_upload ) {
			$files     = $files_upload['name'];
			$files_tmp = $files_upload['tmp_name'];
		}

		// get content of ticket
		$content = JRequest::getVar( 'ticket_message', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$content = obHelpDeskHelper::bbcodeToHtml( $content );
		$session->set( 'obhelpdesk_content', $content );

		// departmentID
		$did = $session->get( 'did' );
		// get department information.
		$department = obHelpDeskTicketHelper::getDepartment( $did );

		// check permission on department --> User's Groups
		$DeparmentPermission = obHelpDeskUserHelper::checkDepartmentPermission( $department->usergroups, $user->id );
		if ( ! $DeparmentPermission ) {
			$msg = JText::_( 'COM_OBHELPDESK_MSG_NO_PERMISSION_IN_DEPARTMENT' );
			$this->setMessage( $msg, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=error' ) );
		}

		// check captcha entered
		$utility_recaptcha_enable = obHelpDeskHelper::getConfig( 'utility_recaptcha_Enable' );
		$reCAPTCHA_Enable         = ( isset( $utility_recaptcha_enable->value ) && ! $user->id ) ? $utility_recaptcha_enable->value : false;
		if ( $reCAPTCHA_Enable ) {
			obHelpDeskHelper::reCaptchaProcess();
		}

		// check field enter
		$required = self::checkRequired( $data, $content, $user );
		if ( ! $required ) {
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=ticket&task=newticket&did=' . $did, false ) );

			return false;
		}

		// check custom field required

		// Check permission upload files && Extensions Allowed.
		$uploadPermission = obHelpDeskUserHelper::checkPermissionUpload( $department->file_upload, $logged );
		$extPermission    = obHelpDeskUserHelper::checkFilesUpload( $files, $department->file_upload_extensions );
		if ( ! $uploadPermission or ( $uploadPermission && ! $extPermission ) ) {
			$msg_error = JText::_( 'DEPARTMENT_NOT_ALLOW_UPLOAD' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=ticket&task=newticket&did=' . $did, false ) );

			return false;
		}

		$ticket = JTable::getInstance( 'Ticket', 'obHelpDeskTable' );
		// Initial

		$ticket->staff = 0;
		if ( ! $user->id ) {
			$ticket->customer_email    = $data['email'];
			$ticket->customer_fullname = $data['fullname'];
		}
		// Process Assign Ticket
		$assignment_type = $department->assignment_type;

		if ( ! obHelpDeskTicketHelper::assignUserTicket( $ticket, $user, $data, $assignment_type ) ) {
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=ticket&task=newticket&did=' . $did ) );

			return false;
		}

		# auto assign
		if ( $department->assignment_type == 'automatic' && ! $ticket->staff ) {
			// get free staff
			$staff_id      = obHelpDeskHelper::getFreeStaff( $department->id );
			$ticket->staff = $staff_id;
		}
		$ticket->subject      = $data['subject'];
		$ticket->priority     = $data['priority'];
		$ticket->departmentid = $did;
		$ticket->status       = 'open';

		$time              = JFactory::getDate();
		$ticket->created   = $time->toSQL();
		$ticket->updated   = $time->toSQL();
		$ticket->quickcode = obHelpDeskHelper::generateQuickCode( $ticket->created );
		$ticket->info      = obHelpDeskTicketHelper::getAdditionInfo( $logged );

// 		store ticket's information.
		if ( ! $ticket->store() ) {
			$msg_error = JText::_( 'Can not store the ticket to database.' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=ticket&task=newticket&did=' . $did, false ) );

			return false;
		}
		// Upload file process.
		$attachment = obHelpDeskTicketHelper::UploadFiles( $files, $files_tmp );
		// save content of ticket to message table.
		$message = JTable::getInstance( 'TicketMessage', 'obHelpDeskTable' );

		$message->tid        = $ticket->id;
		$message->user_id    = $ticket->customer_id;
		$message->email      = $ticket->customer_email;
		$message->content    = $content;
		$message->reply_time = $time->toSQL();
		$message->files      = ( $files ) ? implode( "\n", $files ) : '';

		if ( ! $message->store() ) {
			$msg_error = JText::_( 'OBHELPDESK_NEWTICKET_ERROR_CAN_NOT_STORE' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=ticket&task=newticket&did=' . $did, false ) );

			return false;
		}

		/* Store last message ID to ticket table */
		$ticket->first_msg_id = $message->id;
		$ticket->last_msg_id  = $message->id;

		if ( ! $ticket->store() ) {
			JError::raiseError( 500, $ticket->getError() );
			$msg_error = JText::_( 'Can not store content of ticket to database.' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=ticket&task=newticket&did=' . $did, false ) );

			return false;
		}

		// save custom field of ticket.
		$fields = isset( $data['field'] ) ? $data['field'] : array();

		obHelpDeskHelper::saveFields( $fields, $ticket->id, $did );

		if ( ! $user->id ) {
			#TODO: Create new user
			$new_user         = obHelpDeskUserHelper::createUser( $ticket->customer_fullname, $ticket->customer_email );
			$message->user_id = $new_user->id;
			$message->store();
			$ticket->customer_id = $new_user->id;
			$ticket->replies     = 1;
			$ticket->store();
		}

		// send mail. ( check config information).
// 		$body = obHelpDeskHelper::bbcodeToHtml($content);
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

		if ( $user->id ) {
			$is_staff = obHelpDeskUserHelper::is_staff( $user->id );
			if ( $is_staff ) {
				if ( $department->user_email_ticket ) {
					$arg['staff'] = $user;
					obHelpDeskHelper::SendMail( 'add_ticket_customer', $ticket->customer_email, $ticket->customer_fullname, $body, $ticket, $attachment, $arg );
				}
			} else {
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

					if ( $staff_ids && count( $staff_ids ) ) {
						foreach ( $staff_ids as $staff_id ) {
							$staff        = JFactory::getUser( $staff_id );
							$arg['staff'] = $staff;
							obHelpDeskHelper::SendMail( 'add_ticket_staff', $staff->email, $staff->name, $body, $ticket, $attachment, $arg );
						}
					}
				}
			}
		}

		// redirect when submit successfull
		$this->setMessage( JText::_( 'COM_OBHELPDESK_SUBMITED_SUCCESS' ), 'message' );
		if ( obHelpDeskUserHelper::is_staff( $user->id ) ) { // IF STAFF
			if ( obHelpDeskHelper::getConfig( 'reply_redirection_staff' ) == 'stay' ) {
				$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=ticket&task=newticket&did=' . $did, false ) );
			} else {
				$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=tickets&task=list', false ) );
			}
		} elseif ( $logged ) { // IF Customer
			if ( obHelpDeskHelper::getConfig( 'reply_redirection_customer' ) == 'stay' ) {
				$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=ticket&task=newticket&did=' . $did, false ) );
			} else {
				$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=tickets&task=list', false ) );
			}
		} else { // IF not Login.
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=ticket&task=newticket&did=' . $did, false ) );
		}
		$session->set( 'obhelpdesk_data', null );
		$session->set( 'obhelpdesk_content', null );

		return true;
	}

	function checkRequired( $data, $content, $user ) {
		$app    = JFactory::getApplication();
		$return = false;
		if ( ! trim( $content ) ) {
			$msg_error = JText::_( 'COM_OBHELPDESK_REQUIRE_CONTENT' );
			$this->setMessage( $msg_error, 'error' );
		}

		if ( ! trim( $data['subject'] ) ) {
			$msg_error = JText::_( 'COM_OBHELPDESK_REQUIRE_SUBJECT' );
			$this->setMessage( $msg_error, 'error' );
		}

		if ( ! $user->id ) {
			if ( ! trim( $data['fullname'] ) ) {
				$msg_error = JText::_( 'COM_OBHELPDESK_REQUIRE_FULLNAME' );
				$this->setMessage( $msg_error, 'error' );
			}

			if ( ! trim( $data['email'] ) ) {
				$msg_error = JText::_( 'COM_OBHELPDESK_REQUIRE_EMAIL' );
				$this->setMessage( $msg_error, 'error' );
			}

			if ( ! obHelpDeskHelper::is_email( $data['email'] ) ) {
				$msg_error = JText::_( 'COM_OBHELPDESK_REQUIRE_EMAIL' );
				$this->setMessage( $msg_error, 'error' );
			}
		}

		$return = true;

		return $return;
	}

	function update() {

		$user    = JFactory::getUser();
		$session = JFactory::getSession();
		$ticket  = JTable::getInstance( 'Ticket', 'obHelpDeskTable' );
// 		$tid = (int) JRequest::getVar('tid');
		$tid = (int) JRequest::getVar( 'id' );
		$ticket->load( $tid );
		if ( ! obHelpDeskUserHelper::is_staff( $user->id ) ) {
			$msg_error = JText::_( 'COM_OBHELPDESK_MSG_REQUIRE_STAFF_ACCOUNT' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, false ) );

			return false;
		}
		$data = JRequest::getVar( 'jform', array(), 'post', 'array' );
		$sid  = JRequest::getVar( 'staff_id', '_none' );
		if ( ( (int) $sid > 0 ) && obHelpDeskUserHelper::checkPermission( $user->id, 'assign_tickets' ) ) {
			$ticket->staff = (int) $sid;
		}

		if ( $data['status'] && obHelpDeskUserHelper::checkPermission( $user->id, 'change_ticket_status' ) ) {
			$ticket->status = $data['status'];
		}

		if ( obHelpDeskUserHelper::checkPermission( $user->id, 'update_ticket' ) ) {
			$ticket->subject      = $data['subject'];
			$ticket->priority     = $data['priority'];
			$ticket->departmentid = $data['departmentid'];
			// save custom field of ticket.
			$fields = isset( $data['field'] ) ? $data['field'] : array();
			obHelpDeskHelper::saveFields( $fields, $ticket->id, $ticket->departmentid, true );
		}
		if ( ! $ticket->store() ) {
			$msg_error = JText::_( 'Error update database' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, false ) );

			return false;
		}

		$msg = JText::_( 'Successfull !!!' );
		$this->setMessage( $msg, 'message' );
		$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, true ) );

		return true;
	}

	/********** REPLY TICKET **********/
	function replyopen() {
		$this->reply();
	}

	function replyclose() {
		$this->reply();
	}

	function replyonhold() {
		$this->reply();
	}

	function reply() {

		$app  = JFactory::getApplication();
		$db   = JFactory::getDbo();
		$user = JFactory::getUser();
		if ( ! $user->id ) {
			#get userid
			$obhelpdesk_logged = $app->getUserState( 'obhelpdesk_logged', true );
			$email             = $app->getUserState( 'obhelpdesk_ticket_email' );
			$code              = $app->getUserState( 'obhelpdesk_ticket_code' );
			$sql               = "SELECT * FROM `#__users` WHERE `email`='{$email}'";
			$db->setQuery( $sql );
			$user = $db->loadObject();
		}
		$session    = JFactory::getSession();
		$ticket     = JTable::getInstance( 'Ticket', 'obHelpDeskTable' );
		$message    = JTable::getInstance( 'TicketMessage', 'obHelpDeskTable' );
		$department = JTable::getInstance( 'Department', 'obHelpDeskTable' );
		$staff      = JTable::getInstance( 'Staff', 'obHelpDeskTable' );
		$group      = JTable::getInstance( 'Group', 'obHelpDeskTable' );

		$tid = (int) JRequest::getVar( 'id' );
		$ticket->load( $tid );

		// check reply permission
		if ( ! obHelpDeskUserHelper::checkReplyTicketPermission( $user->id, $tid ) ) {
			$msg_error = JText::_( 'COM_OBHELPDESK_MSG_NO_PERMISSION_REPLY' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, false ) );

			return false;
		}

		$email = ( $user->email ) ? $user->email : ( ( $session->get( 'obhelpdesk_email' ) ) ? $session->get( 'obhelpdesk_email' ) : '' );

		// get reply content
		$content = JRequest::getVar( 'ticket_message', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$content = obHelpDeskHelper::bbcodeToHtml( $content );
		// get files uploaded information.
		$files        = null;
		$files_tmp    = null;
		$files_upload = JRequest::getVar( 'FileName', null, 'files', 'array' );
		if ( $files_upload ) {
			$files     = $files_upload['name'];
			$files_tmp = $files_upload['tmp_name'];
		}
		$time = JFactory::getDate();

		$message->content    = $content;
		$message->tid        = $tid;
		$message->user_id    = $user->id;
		$message->email      = $email;
		$message->reply_time = $time->toSQL();
		$message->files      = ( $files ) ? implode( "\n", $files ) : '';

		if ( ! $message->store() ) {
			$msg_error = JText::_( 'Insert Message Error' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, false ) );

			return false;
		}

		$ticket->last_msg_id = $message->id;
		$task                = $this->getTask();
		#TODO: 
		$isStaff = obHelpDeskUserHelper::is_staff( $user->id );
		if ( ! $ticket->staff && $isStaff ) {
			$ticket->staff = $user->id;
		}
		switch ( $task ) {
			case 'replyopen':
				$ticket->status = 'open';
				break;
			case 'replyonhold':
				$ticket->status = 'on-hold';
				break;
			case 'replyclose':
				$ticket->status = 'closed';
				break;
			case 'reply':
			default:
				if ( $isStaff ) {
					$ticket->status = 'on-hold';
				} else {
					$ticket->status = 'open';
				}
				break;
		}

		#TODO: Update number of relplies
		$query = "SELECT COUNT(*) FROM `#__obhelpdesk3_messages` WHERE `tid`=" . (int) $tid;
		$db->setQuery( $query );
		$replies         = $db->loadResult();
		$ticket->replies = $replies;

		#TODO: Save 
		if ( ! $ticket->store() ) {
			$msg_error = JText::_( 'Update Ticket Error' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, false ) );

			return false;
		}

		// Upload file process.
		$attachment = obHelpDeskTicketHelper::UploadFiles( $files, $files_tmp );

		// send mail. ( check config information).
// 		$body = obHelpDeskHelper::bbcodeToHtml($content);
		$body = $content;

		$cuser_is_staff = ( $user->id && obHelpDeskUserHelper::is_staff( $user->id ) );
		$department->load( $ticket->departmentid );
		$params = array( 'message' => $message, 'department' => $department );

		if ( $cuser_is_staff ) {
			#TODO: send mail "new ticket reply" to customer
			if ( $department->user_email_ticket ) {
				obHelpDeskHelper::SendMail( 'add_ticket_reply_customer', $ticket->customer_email, $ticket->customer_fullname, $body, $ticket, $attachment, $params );
			}

			if ( $user->id != $ticket->staff && $department->staff_email_ticket ) {
				$ticket_staff = JFactory::getUser( $ticket->staff );
				#TODO: send "new ticket reply" email to ticket staff
				obHelpDeskHelper::SendMail( 'add_ticket_reply_staff', $ticket_staff->email, $ticket_staff->name, $body, $ticket, $attachment, $params );
			}

		} else {
			if ( $department->staff_email_ticket ) {
				$ticket_staff = JFactory::getUser( $ticket->staff );
				#TODO: send "new ticket reply" email to ticket staff
				obHelpDeskHelper::SendMail( 'add_ticket_reply_staff', $ticket_staff->email, $ticket_staff->name, $body, $ticket, $attachment, $params );
			}
		}

		$msg = JText::_( 'COM_OBHELPDESK_REPLY_SUBMITED_SUCCESS' );
		$this->setMessage( $msg, 'message' );
		$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, true ) );

		return true;
	}

	function close() {
		$db     = JFactory::getDBO();
		$ticket = JTable::getInstance( 'Ticket', 'obHelpDeskTable' );
		$tid    = (int) JRequest::getVar( 'id' );
		$ticket->load( $tid );
		$ticket->status = 'closed';
		if ( ! $ticket->store() ) {
			$msg_error = JText::_( 'Error update database' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, false ) );

			return false;
		}

		$msg = JText::_( 'Closed Success' );
		$this->setMessage( $msg, 'message' );
		$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, true ) );

		return true;
	}

	function reopen() {
		$db      = JFactory::getDBO();
		$session = JFactory::getSession();
		$ticket  = JTable::getInstance( 'Ticket', 'obHelpDeskTable' );

		$tid = (int) JRequest::getVar( 'id' );
		$ticket->load( $tid );
		$ticket->status = 'open';
		if ( ! $ticket->store() ) {
			$msg_error = JText::_( 'Error update database' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, false ) );

			return false;
		}

		$msg = JText::_( 'Re-Open Success' );
		$this->setMessage( $msg, 'message' );
		$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, true ) );

		return true;
	}

	function remove() {
		$db      = JFactory::getDBO();
		$session = JFactory::getSession();
		$ticket  = JTable::getInstance( 'Ticket', 'obHelpDeskTable' );

		$tid = $session->get( 'obhelpdesk_tid' ) ? $session->get( 'obhelpdesk_tid' ) : (int) JRequest::getVar( 'id' );
		$ticket->load( $tid );
		$ticket->status = 'closed';
		if ( ! $ticket->delete() ) {
			$msg_error = JText::_( 'Error remove' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=tickets&task=list', false ) );

			return false;
		}

		$msg = JText::_( 'Removed success' );
		$this->setMessage( $msg, 'message' );
		$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=tickets&task=list', true ) );

		return true;
	}

	function delmsg() {
		$db      = JFactory::getDBO();
		$session = JFactory::getSession();
		$ticket  = JTable::getInstance( 'Ticket', 'obHelpDeskTable' );
		$message = JTable::getInstance( 'TicketMessage', 'obHelpDeskTable' );
		$msgid   = JRequest::getVar( 'obhelpdesk_msg_id', 0, '', 'int' );
		$tid     = $session->get( 'obhelpdesk_tid' ) ? $session->get( 'obhelpdesk_tid' ) : (int) JRequest::getVar( 'id' );
		if ( ! $tid or ! $msgid ) {
			$msg_error = JText::_( 'Error' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=tickets&task=list', false ) );
		}

		$query = "SELECT MIN(`id`) FROM `#__obhelpdesk3_messages` WHERE `tid` = " . $tid;
		$db->setQuery( $query );
		$min = $db->loadResult();

		// Get First Message ID
		if ( $msgid == $min ) {
			$msg_error = JText::_( 'Cannot remove root message' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, false ) );

			return false;
		}

		// Get Last message ID
		$query = "SELECT MAX(`id`) FROM `#__obhelpdesk3_messages` WHERE `tid` = " . $tid;
		$db->setQuery( $query );
		$max = $db->loadResult();
		$message->load( $msgid );
		if ( ! $message->delete() ) {
			$msg_error = JText::_( 'Error remove' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, false ) );

			return false;
		}

		if ( $msgid == $max ) {
			$query = "SELECT MAX(`id`) FROM `#__obhelpdesk3_messages` WHERE `tid` = " . $tid;
			$db->setQuery( $query );
			$max_new = $db->loadResult();
			$ticket->load( $tid );
			$ticket->last_msg_id = $max_new;
			if ( ! $ticket->store() ) {
				$msg_error = JText::_( 'Error update last message ID' );
				$this->setMessage( $msg_error, 'error' );
				$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, false ) );

				return false;
			}
		}
		$msg = JText::_( 'Removed success' );
		$this->setMessage( $msg, 'message' );
		$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, true ) );

		return true;
	}

	function updatemsg() {
		$db      = JFactory::getDBO();
		$session = JFactory::getSession();
		$message = JTable::getInstance( 'TicketMessage', 'obHelpDeskTable' );

		$msgid = JRequest::getVar( 'obhelpdesk_msg_id', 0, '', 'int' );
		$tid   = $session->get( 'obhelpdesk_tid' ) ? $session->get( 'obhelpdesk_tid' ) : (int) JRequest::getVar( 'id' );
		if ( ! $tid or $msgid == 0 ) {
			$msg_error = JText::_( 'Error' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, false ) );

			return false;
		}

		$content = JRequest::getVar( 'obhelpdesk-edit-content-msg-' . $msgid, '', 'post', 'string', JREQUEST_ALLOWRAW );
		if ( ! $content ) {
			$msg_error = JText::_( 'Empty message !!!' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, false ) );

			return false;
		}

		$message->load( $msgid );
		$message->content = $content;
		if ( ! $message->store() ) {
			$msg_error = JText::_( 'Error Update' );
			$this->setMessage( $msg_error, 'error' );
			$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, false ) );

			return false;
		}
		$msg = JText::_( 'Update success' );
		$this->setMessage( $msg, 'message' );
		$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $tid, true ) );

		return true;
	}

	// download an attachment
	function download() {
		$file       = base64_decode( JRequest::getVar( 'file' ) );
		$alias      = substr( end( explode( DS, $file ) ), 14 );
		$view_types = array( 'png', 'jpg' , 'gif' , 'jpeg' , 'html' , 'htm' , 'txt' , 'pdf' , 'text' , 'xhtml' , 'xml' , 'rtf' , 'ico' , 'rss' , 'feed' , 'atom' );
		clearstatcache();

		$len            = filesize( $file );
		$filename       = basename( $file );
		$file_extension = strtolower( substr( strrchr( $filename, "." ), 1 ) );
		$ctype          = obHelpDeskHelper::datei_mime( $file_extension );
		ob_end_clean();
		// needed for MS IE - otherwise content disposition is not used?
		if ( ini_get( 'zlib.output_compression' ) ) {
			ini_set( 'zlib.output_compression', 'Off' );
		}
		header( "Cache-Control: public, must-revalidate" );
		header( 'Cache-Control: pre-check=0, post-check=0, max-age=0' );
		// header("Pragma: no-cache");  // Problems with MS IE
		header( "Expires: 0" );
		header( "Content-Description: File Transfer" );
		header( "Content-Type: " . $ctype );

		if ( ! in_array( $file_extension, $view_types ) ) {
			header( 'Content-Disposition: attachment; filename="' . $alias . '"' );
		} else {
			// view file in browser
			header( 'Content-Disposition: inline; filename="' . $alias . '"' );
		}
		header( "Content-Transfer-Encoding: binary\n" );
		header( "Content-Length: " . (string) $len );
		// set_time_limit doesn't work in safe mode
		//if (!ini_get('safe_mode')){
		//    @set_time_limit(0);
		//}
		@readfile( $file );
		exit;
	}


	/* * * * * * * * * * * * * * * *
	 * Load FAQ
	 * * * * * * * * * * * * * * * */
	public function loadfaq() {
		JPluginHelper::importPlugin( 'obhelpdesk_kb' );
		$dispatcher    = JDispatcher::getInstance();
		$res           = array();
		$str_search    = JRequest::getVar( 'str' );
		$str           = "";
		$department_id = JRequest::getVar( 'department' );
		# Get config of com_obhelpdesk
		$hd_configs  = JComponentHelper::getParams( 'com_obhelpdesk' );
		$faq_manager = $hd_configs->get( 'faq_manager', '' );
		#get config of faq plugin

		# Get categories
		$db  = JFactory::getDbo();
		$sql = "SELECT `kb_catid` FROM `#__obhelpdesk3_departments` WHERE `id`={$department_id}";
		$db->setQuery( $sql );
		$kb_catid = $db->loadResult();
		$catids   = explode( ',', $kb_catid );

		$dispatcher->trigger( 'onobHelpDeskFaqSearch', array( &$str_search, &$res, $faq_manager, $catids ) );
		if ( count( $res ) ) {
			$jv    = new JVersion();
			$isJ25 = ( $jv->RELEASE == '2.5' );
			$arr   = array();
			foreach ( $res as $v ) {
				$arr[] = $v->id;
			}
			$count = count( $res );
			echo '
				<div class="panel-group" id="accordion">
			';
			for ( $i = 0; $i < $count; $i ++ ) {
				$obj = array_pop( $res );
				array_pop( $arr );
				$in   = ( $i == 0 ) ? '' : ' in';
				$href = $isJ25 ? "" : "#ob_answer_'.$obj->id.'";
				if ( ! in_array( $obj->id, $arr ) || empty( $arr ) ) {
					echo '
						<div class="panel panel-default">
							<div class="panel-heading">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse' . $obj->id . '">
									' . $obj->questions . '
								</a>
							</div>
							<div id="collapse' . $obj->id . '" class="panel-collapse collapse">
								<div class="panel-body">
									' . $obj->answers . '
									<p class="text-right">
										<a href="' . $obj->link . '" target="_blank" title="read more" class="btn btn-small">read more</a>
									</p>
								</div>
							</div>
						</div>
					';
				}
			}
			echo '
				</div>
			';
		}
		jexit();
	}


	public function loadmoremsg() {
		$user = JFactory::getUser();
		$app  = JFactory::getApplication();
		$tid  = JRequest::getVar( 'tid' );

		$is_staff = obHelpDeskUserHelper::is_staff( $user->id );
		// Check Permission View ticket
		if ( ! obHelpDeskUserHelper::checkViewTicketPermission( $user->id, $tid, $is_staff ) ) {
			jexit( '' );
		}

		$last_msg_id = JRequest::getVar( 'last_msg_id' );

		if ( (int) $tid == 0 ) {
			jexit( '' );

			return false;
		}

		$db = JFactory::getDbo();
		#TODO: get first msg id in table
		$query = "SELECT 
					    `first_msg_id`
					FROM
					    `#__obhelpdesk3_tickets`
					WHERE 
						`id`=" . $tid;
		$db->setQuery( $query );
		$first_msg_id = $db->loadResult();


		$query = "SELECT 
						m.*, 
						u.email as umail, 
						u.name as uname, 
						oc.email as cmail, 
						oc.fullname as cname
					FROM 
						`#__obhelpdesk3_messages` as m 
						LEFT JOIN `#__users` as u ON m.`user_id` = u.`id` 
						LEFT JOIN `#__obhelpdesk3_customers` as oc ON m.`email` = oc.`email` 
					WHERE 
						m.`tid`={$tid}
						AND m.`id`<{$last_msg_id}
					ORDER BY m.`id` DESC
					LIMIT 5";
		$db->setQuery( $query );
		$msgs = $db->loadObjectList();
		if ( $db->getErrorNum() ) {
			jexit();
		}
		$count_msgs = count( $msgs );
		if ( ! count( $msgs ) ) {
			jexit();
		}

// 		ob_start();
		$i = 1;
		foreach ( $msgs as $msg ) {
			$uname          = ( $msg->uname ) ? $msg->uname : $msg->cname;
			$umail          = ( $msg->umail ) ? $msg->umail : $msg->cmail;
			$avatar         = obHelpDeskUserHelper::getProfileAvatar( $msg->user_id, 24 );
			$profile_link   = obHelpDeskUserHelper::getProfileLink( $msg->user_id );
			$in             = '';
			$org_content    = $msg->content;
			$raw_content    = mb_substr( strip_tags( $org_content ), 0, 300 );
			$class_hasfiles = ( $msg->files ) ? ' obhd_hasfiles' : '';
			?>
			<div class="obhd_message_wrap<?php echo $in . $class_hasfiles; ?>" id="obhd_messsage_<?php echo $msg->id; ?>">
				<div class="message_heading" onClick="obHDToogleMessage(<?php echo $msg->id; ?>)">
					<table class="table">
						<tbody>
						<tr>
							<td class="obhd_heading_left">
								<div class="obhd_heading_left_content">
									<?php
									#TODO: load avata plugins
									if ( $avatar ) {
										echo '<div class="obhelpdesk-message-info-avatar">
													<img class="" src="' . $avatar . '" alt="' . $uname . '" title="' . $uname . '" height="48" class="hasTip" />
													</div>';
									}

									#TODO: display username
									echo '<span class="obhd_username">' . $uname . '</span>';

									#TODO: trigger plugin
									echo '<span class="obhd_message_plugins">';
									JPluginHelper::importPlugin( 'obhelpdesk' );
									$dispatcher = JDispatcher::getInstance();
									$results = $dispatcher->trigger( 'onLoadReply', array( &$msg ) );
									echo '</span>';

									#TODO: display raw message content
									echo '<span class="obhd_raw_content">' . $raw_content . '</span>';

									?>
								</div>
							</td>
							<td class="obhd_heading_right">
								<?php
								if ( $msg->files ) {
									echo '<span class="icon-flag-2"></span>';
								}
								?>
								<span class="obhd_reply_time"><?php echo obHelpDeskHelper::facebookTime( $msg->reply_time ); ?></span>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="message_body" id="obhd_message_body<?php echo $msg->id; ?>">
					<div class="org_message_body"><?php echo $org_content; ?></div>
					<?php if ( $msg->files ): ?>
						<div class="obhelpdesk-message-attachments">
							<hr />
							<?php $arr_files = explode( "\n", $msg->files ); ?>
							<?php
							for ( $j = 0; $j < count( $arr_files ); $j ++ ) {
								$file     = $arr_files[$j];
								$time     = JFactory::getDate( $msg->reply_time )->format( 'YmdHis' );
								$filepath = JPATH_COMPONENT . DS . 'uploads' . DS . $time . $arr_files[$j];
								if ( file_exists( $filepath ) ):
									$file_url = JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.download&msg_id=' . $msg->id . '&file=' . base64_encode( $filepath ) );
									?>
									<a target="_blank" href="<?php echo $file_url; ?>">
										<i class="icon-download-alt"></i><?php echo $file; ?>
									</a>
									<small>(<?php echo intval( filesize( $filepath ) / 1024 ); ?> Kb)</small>
									<?php
									// render image if the file is an image
									if ( exif_imagetype( $filepath ) ) {
										echo "<img src='{$file_url}' class='img-responsive thumbnail' />";
									}
									?>
									<br />
								<?php
								endif;
							}
							?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php
			++$i;
		}
		if ( $msgs[$count_msgs - 1]->id == $first_msg_id ) {
			echo '<script>$("obhd_load_more_messages").hide();</script>';
		}
		jexit();
	}

	public function loadcustomfield() {
		$did    = JRequest::getVar( 'did' );
		$tid    = JRequest::getVar( 'tid' );
		$fields = obHelpDeskTicketHelper::getFields( $did, $tid );
		$res    = array();
		$html   = '';
		if ( count( $fields ) ) :
			$html .= '<table>';
			foreach ( $fields as $field ) :
				$res[] = array( 'td1' => '<label id="jform_obhelpdesk_' . $field->id . '-lbl" for="jform_obhelpdesk_' . $field->id . '" class="hasTip" title="' . $field->title . '::' . $field->helptext . '" >' . $field->title . ( ( $field->required ) ? '<code>*</code>' : '' ) . '</label>',
								'td2' => obHelpDeskFieldsHelper::printField( $field ) );

				$html .= '<tr>
					<td style="text-align: right;">
						<label id="jform_obhelpdesk_' . $field->id . '-lbl" for="jform_obhelpdesk_' . $field->id . '" class="hasTip" title="' . $field->title . '::' . $field->helptext . '" >' . $field->title . ( ( $field->required ) ? '<code>*</code>' : '' ) . '</label>
						</td>
						<td>' . obHelpDeskFieldsHelper::printField( $field ) . '</td>
					</tr>';
			endforeach;
			$html .= '</table>';
		endif;

		echo json_encode( $res );
// 		echo $res;
		exit();
	}
}