<?php
/**
* @package		$Id: helper.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	Copyright (C) 2007-2010 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_SITE.DS.'components'.DS.'com_obhelpdesk'.DS.'helpers'.DS.'obhelpdesk.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_obhelpdesk'.DS.'helpers'.DS.'user.php');
$limit 	= $params->get('itemCount', 10);
?>