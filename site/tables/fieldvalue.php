<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
 
// import Joomla table library
jimport('joomla.database.table');

class obHelpDeskTableFieldValue extends JTable
{
	var $id 			= null;
	var $field_id	 	= null;
	var $department_id 	= null;
	var $ticket_id 		= null;
	var $value 			= null;

	function obHelpDeskTableFieldValue(& $db)
	{
	    parent::__construct('#__obhelpdesk3_field_values', 'id', $db);
	}
}
?>