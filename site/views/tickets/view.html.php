<?php

// ensure a valid entry point
defined( '_JEXEC' ) or die( 'Restricted Access' );

jimport( 'joomla.application.component.view' );

class obHelpDeskViewTickets extends obView {
	protected $items;
	protected $pagination;
	protected $state;

	function display( $tpl = null ) {
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		//$menu->topnav();
		if ( ! obHelpDeskUserHelper::CheckPermissionViewListTicket( $user->id ) ) {
			// if have not permission
			if ( $this->getLayout() != 'entercode' && $this->getLayout() != 'changecode' ) {
				$msg = JText::_( 'NO_PERMISSION' );
				$app->enqueueMessage( $msg, 'warning' );
			}
// 			$this->setLayout('entercode');
			$uri    = JURI::getInstance();
			$return = base64_encode( $uri->toString() );
			$msg    = JText::_( 'COM_OBHELPDESK_REQUIRED_LOGIN' );
			$app->redirect( 'index.php?option=com_users&view=login&Itemid=141&return=' . $return, $msg, 'error' );
		} elseif ( $this->getLayout() == 'entercode' ) {
			$this->setLayout( 'default' );
		}

		if ( $this->getLayout() == 'default' ) {
			$this->items = $this->get( 'Items' );
// 			echo "<pre>"; print_r($this->items);
			$this->pagination = $this->get( 'Pagination' );
			$this->state      = $this->get( 'State' );

			$this->listDepartment = $this->get( 'FilterDepartmentList' );
			$is_staff             = obHelpDeskUserHelper::is_staff( $user->id );

			if ( $is_staff ) {
				$this->listStaff = $this->get( 'FilterStaffList' );
				$default_status  = 'open';
			} else {
				$this->listStaff = false;
				$default_status  = 'on-hold';
			}

			$status = $app->getUserStateFromRequest( "com_obhelpdesk.filter_status", 'filter_status', $default_status );

			$model            = $this->getModel();
			$this->listStatus = $model->getFilterStatusList( $status );

			$this->filter_from = JRequest::getVar( 'filter_from' );
			$this->filter_to   = JRequest::getVar( 'filter_to' );

			// check permission for bulk operator
			$this->showBulk         = false;
			$this->listBulkPriority = false;
			$this->listBulkStatus   = false;
			$this->BulkUpdate       = false;
			$this->BulkDelete       = false;
			$this->listBulkAssignee = false;
			if ( $is_staff ) {
				if ( obHelpDeskUserHelper::checkPermission( $user->id, 'change_ticket_status' ) ) {
					$this->listBulkPriority = $this->get( 'BulkPriority' );
					$this->listBulkStatus   = $this->get( 'BulkStatus' );
					$this->showBulk         = true;
				}
				if ( obHelpDeskUserHelper::checkPermission( $user->id, 'update_ticket' ) && ( $this->listBulkPriority or $this->listBulkStatus or $this->listBulkAssignee ) ) {
					$this->BulkUpdate = true;
					$this->showBulk   = true;
				}
				if ( obHelpDeskUserHelper::checkPermission( $user->id, 'delete_ticket' ) ) {
					$this->BulkDelete = true;
					$this->showBulk   = true;
				}

				if ( obHelpDeskUserHelper::checkPermission( $user->id, 'assign_tickets' ) ) {
					$this->listBulkAssignee = obHelpDeskUserHelper::getStaffList();
					$this->showBulk         = true;
				}
			}
		}
		parent::display( $tpl );
	}

} // end class
?>