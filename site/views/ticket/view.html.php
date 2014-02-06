<?php

// ensure a valid entry point
defined( '_JEXEC' ) or die( 'Restricted Access' );

jimport( 'joomla.application.component.view' );

class obHelpDeskViewTicket extends obView {
	public $de_arr = array();
	protected $form;
	protected $data;

	function display( $tpl = null ) {
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		$submit_without_login = obHelpDeskHelper::getConfig( 'submit_without_login' )->value;

		if ( ! $submit_without_login && $user->guest ) {
			$juri   = JURI::getInstance();
			$return = base64_encode( $juri->toString() );
			$app->redirect( 'index.php?option=com_users&view=login&Itemid=141&return=' . $return ); // @todo: auto detect Itemid
		} elseif ( $submit_without_login && $user->guest ) {
			/*
						$obhelpdesk_logged = $app->getUserState('obhelpdesk_logged');
						if( $obhelpdesk_logged ) {
							$obhelpdesk_user = $app->getUserState( 'obhelpdesk_user');
							$this->data['fullname']= $obhelpdesk_user->name;
							$this->data['email']= $obhelpdesk_user->email;
						}
			*/
		}

		$session        = JFactory::getSession();
		$did            = JRequest::getInt( 'did' );
		$this->is_staff = false;
		$this->form     = $this->get( 'Form' );
		$session->set( 'obhelpdesk_tid', null );
		if ( $did ) {
			$session->set( 'did', $did );
			// get entered data when submit failed.
			if ( $session->get( 'obhelpdesk_data' ) ) {
				$this->data = $session->get( 'obhelpdesk_data' );
			}
			if ( $session->get( 'obhelpdesk_content' ) ) {
				$this->content = $session->get( 'obhelpdesk_content' );
			}

			if ( ! obHelpDeskTicketHelper::isDepartment( $did ) ) {
				// if have not permission.
				$msg = JText::_( 'COM_OBHELPDESK_MSG_DENIED' );
				$app->redirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=error' ), $msg, 'error' );
			}

			// Force-Redirect to External Link for External Department
			if ( $external_link = obHelpDeskTicketHelper::isExternalDepartment( $did ) ) {
				$app->redirect( $external_link );
			}

			// Set layout
			$this->setLayout( 'newticket' );
			$this->department = obHelpDeskTicketHelper::getDepartment( $did );
			$this->fields     = obHelpDeskTicketHelper::getFields( $did );

			$DeparmentPermission = obHelpDeskUserHelper::checkDepartmentPermission( $this->department->usergroups, $user->id );
			if ( ! $DeparmentPermission ) {
				$msg = JText::_( 'COM_OBHELPDESK_MSG_DENIED' );
				$app->redirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=error' ), $msg, 'error' );
			}

			if ( $user->id ) {
				$this->is_staff = obHelpDeskUserHelper::is_staff( $user->id );
				if ( $this->is_staff ) {
					if ( ! obHelpDeskUserHelper::checkPermission( $user->id, 'add_ticket' ) ) {
						// IF have not permission to create ticket.
						$msg = JText::_( 'COM_OBHELPDESK_MSG_DENIED' );
						$app->redirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=error' ), $msg, 'error' );
					}
					$this->add_ticket_staffs = obHelpDeskUserHelper::checkPermission( $user->id, 'add_ticket_staffs' );
					$this->add_ticket_users  = obHelpDeskUserHelper::checkPermission( $user->id, 'add_ticket_users' );
				}
			}

			// require re-captcha
			$recaptcha          = new stdClass();
			$recaptcha->enabled = false;
			if ( ! $user->id ) {
				$utility_recaptcha_enable = obHelpDeskHelper::getConfig( 'utility_recaptcha_Enable' );
				$reCAPTCHA_Enable         = isset( $utility_recaptcha_enable->value ) ? $utility_recaptcha_enable->value : false;
				$recaptcha->enabled       = $reCAPTCHA_Enable;
				if ( $reCAPTCHA_Enable ) {
					// clare RECAPTCHA
					require_once JPATH_COMPONENT . DS . 'helpers' . DS . 'recaptchalib.php';
					// Get a key from https://www.google.com/recaptcha/admin/create
					$recaptcha->publickey = obHelpDeskHelper::getConfig( 'utility_recaptcha_publickey' )->value;
					# the response from reCAPTCHA
					$recaptcha->resp = null;
					# the error code from reCAPTCHA, if any
					$recaptcha->error = null;
				}
			}

			$this->recaptcha = $recaptcha;

			// require BBCode Editor.
			require_once JPATH_COMPONENT . DS . 'helpers' . DS . 'editor_bbcode.php';
			$editor_bbcode = new ObEditorBBcode();
			$value         = '';
			if ( isset( $this->content ) ) {
				$value = $this->content;
			}
			$editor_ticket_message = $editor_bbcode->display( 'ticket_message', $value, array( 'bold', 'italic', 'underline', 'hypelink', 'image', 'list', 'color', 'quote', 'source' ) );
			$this->editor_message  = $editor_ticket_message;

			$attachkey       = obHelpDeskHelper::getConfig( 'utility_attachkey' );
			$this->attachkey = $attachkey->value;
		} else {
			$de_arr       = obHelpDeskTicketHelper::getDepartmentList( $user->id );
			$this->de_arr = $de_arr;
		}
		$this->user = $user;
		parent::display( $tpl );
	}

} // end class
?>