<?php
/**
* @package		$Id: helper.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	Copyright (C) 2007-2010 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modObHelpDeskDepartmentsStatsHelper {
	public static function getData()
	{
		$db		= JFactory::getDbo();
		$query 	= '
			SELECT d.`id` as `id`, d.`title` as `name`, count(t.`departmentid`) as `count`
			FROM `#__obhelpdesk3_departments` as d
			INNER JOIN `#__obhelpdesk3_tickets` as t
			ON d.`id` = t.`departmentid`
			GROUP BY d.`id`
		';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function random_color(){
		mt_srand((double)microtime()*1000000);
		$c = '';
		while(strlen($c)<6){
			$c .= sprintf("%02X", mt_rand(0, 255));
		}
		return $c;
	}
}
?>