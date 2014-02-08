<?php

// No direct access.
defined( '_JEXEC' ) or die;

jimport( 'joomla.application.component.controllerform' );
JTable::addIncludePath( JPATH_COMPONENT . DS . 'tables' );

/**
 * Ticket controller class.
 * @since       1.6
 */
class obHelpDeskControllerReplyTemplate extends JControllerForm {

	function __construct( $default = array() ) {
		parent::__construct( $default );
		// here is where register tasks 
	}

	public function edit( $key = null, $urlVar = null ) {
		$view = $this->getView( 'replytemplates', 'form' );
		// Get/Create the model
		if ( $model = $this->getModel() ) {
			// Push the model into the view (as default)
			$view->setModel( $model, true );
		}

		// Set the layout
		$view->setLayout( 'edit' );

		// Display the view
		$view->display();
	}

	function cancel( $key = 'r_id' ) {
		$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=replytemplates', true ) );

		return true;
	}

	function add() {
		$view = $this->getView( 'replytemplates', 'form' );
		// Get/Create the model
		if ( $model = $this->getModel() ) {
			// Push the model into the view (as default)
			$view->setModel( $model, true );
		}

		// Set the layout
		$view->setLayout( 'edit' );

		// Display the view
		$view->display();
	}

	function save( $key = null, $urlVar = null ) {
		$db      = JFactory::getDbo();
		$user    = JFactory::getUser();
		$time    = JFactory::getDate();
		$id      = (int) JRequest::getVar( 'id', 0 );
		$data    = JRequest::getVar( 'jform' );
		$content = JRequest::getVar( 'ticket_message', '', 'post', 'string', JREQUEST_ALLOWRAW );
		if ( $id ) {
			$query = "UPDATE `#__obhelpdesk3_replytemplates`"
				. " SET `subject`='" . addslashes( $data['subject'] ) . "',"
				. " `content`='" . addslashes( $content ) . "',"
				. " `staff_id` = " . $user->id . ","
				. " `enable` = " . (int) $data['enable'] . ","
				. " `published` = 1,"
				. " `level` = " . (int) $data['level'] . ","
				. " `modified_date`='" . $time->toSQL() . "'"
				. " WHERE `id`=" . $id;
		} else {
			$query = "INSERT INTO `#__obhelpdesk3_replytemplates`"
				. " SET `subject`='" . addslashes( $data['subject'] ) . "',"
				. " `content`='" . addslashes( $content ) . "',"
				. " `enable` = " . (int) $data['enable'] . ","
				. " `published` = 1,"
				. " `level` = " . (int) $data['level'] . ","
				. " `modified_date`='" . $time->toSQL() . "',"
				. " `created_date`='" . $time->toSQL() . "',"
				. " `staff_id` = " . $user->id;
		}

		$db->setQuery( $query );
		$db->query();

		$msg = JText::_( 'COM_OBHELPDESK_MSG_SUCCESS' );
		$this->setMessage( $msg, 'message' );
		$this->setRedirect( JRoute::_( 'index.php?option=com_obhelpdesk&view=replytemplates', true ) );

		return true;
	}
}
