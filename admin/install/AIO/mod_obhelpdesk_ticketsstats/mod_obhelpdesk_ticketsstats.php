<?php
/**
* @package		$Id: mod_obhelpdesk_ticketsstats.php 2 2013-07-30 08:16:00Z thongta $
* @package		obHelpDesk for Joomla
* @subpackage	Tickets Stats module
* @author 		foobla.com
* @copyright	Copyright (C) 2007-2011 foobla.com. All rights reserved.
* @license		GNU/GPL, see LICENSE
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (dirname( __FILE__ ).DS.'helper.php');

#require_once(JPATH_SITE.DS.'components'.DS.'com_obhelpdesk'.DS.'helpers'.DS.'helpers.php');
#require_once(JPATH_SITE.DS.'components'.DS.'com_obhelpdesk'.DS.'helpers'.DS.'api_user_manager.php');
#$theme = ObHelpDeskHelpers::getDefaultTheme();
$width				= $params->get('width', 300);
$height				= $params->get('height', 300);
$backgroundColor	= $params->get('backgroundColor', '#FFFFFF');
$textColor			= $params->get('textColor', '#000000');
$months				= $params->get('months',12);

# write real data to data file
$data = modObHelpDeskTicketsStatsHelper::getAmcolumnData($months);

require(JModuleHelper::getLayoutPath('mod_obhelpdesk_ticketsstats'));
