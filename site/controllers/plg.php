<?php
/**
* @package		$Id: plg.php 2 2013-07-30 08:16:00Z thongta $
* @author 		Name here
* @copyright	Copyright (C) 2007-2010 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// ensure a valid entry point
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.controllerform' );
jimport('joomla.application.component.controller');

class obHelpDeskControllerPlg extends obController
{
	function __construct( $default = array())
	{
		parent::__construct( $default );
	}
	
	function execute()
	{
		$vName 		= JRequest::getCmd('name', 'unknow');
		$vMethod 	= JRequest::getCmd('sub', 'unknow');
		require_once(JPATH_BASE.DS.'plugins'.DS.'obhelpdesk'.DS.$vName.DS.$vName.'Helpers.php');	
		$classname = "ObHelpDesk".$vName."Helpers";
		call_user_func(array($classname,$vMethod));	
	}
	
	
}