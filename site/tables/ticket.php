<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
 
// import Joomla table library
jimport('joomla.database.table');

/**
 * Ticket Table class
 */
class obHelpDeskTableTicket extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(&$db) 
	{
		parent::__construct('#__obhelpdesk3_tickets', 'id', $db);
	}
}
?>