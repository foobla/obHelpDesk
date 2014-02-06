<?php
/**
* @package		$Id: mod_obhelpdesk_customer.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	Copyright (C) 2007-2010 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (dirname( __FILE__ ).DS.'helper.php');

// $limit 	= $params->get('itemCount', 10);
// $who	= $params->get('who');

// $overdueTickets = obHelpDeskHelper::loadOverdueTickets($limit, $who);
require(JModuleHelper::getLayoutPath('mod_obhelpdesk_customer'));
