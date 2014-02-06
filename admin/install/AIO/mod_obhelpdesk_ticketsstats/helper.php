<?php
/**
* @verion		$Id: helper.php 2 2013-07-30 08:16:00Z thongta $
* @package		obHelpDesk for Joomla
* @subpackage	Tickets Stats module
* @author 		foobla.com
* @copyright	Copyright (C) 2007-2011 foobla.com. All rights reserved.
* @license		GNU/GPL, see LICENSE
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modObHelpDeskTicketsStatsHelper
{
	public static function getAmcolumnData($months)
	{
		$db = JFactory::getDBO();
		$qr = '
			SELECT ot.`id`, ot.`created`, ot.`status` 
			FROM `#__obhelpdesk3_tickets` as ot
			ORDER BY ot.`created` ASC
		';
		$db->setQuery($qr);
		$results	= $db->loadObjectList();
		
		$data				= new stdClass();
		$data->months		= array();
		$data->oticket		= array();
		$data->cticket		= array();
		$data->ohticket		= array();
		if (count($results)) {
			$data->months[0]	= date('M/Y', strtotime($results[0]->created));
		} else {
			$data->months[0] = 0;
		}
		$data->oticket[0]	= 0;
		$data->cticket[0]	= 0;
		$data->ohticket[0]	= 0;
				
		for ($i=0, $j=0; $i<count($results); $i++) {
			$tmonth		= date('M/Y',strtotime($results[$i]->created));
			if ($tmonth != $data->months[$j]) {
				$j++;
				$data->months[$j]	= $tmonth;
				$data->oticket[$j]	= 0;
				$data->cticket[$j]	= 0;
				$data->ohticket[$j]	= 0;
			}
			switch ($results[$i]->status) {
				case 'closed'	: $data->cticket[$j]	+= 1;
					break;
				case 'open'		: $data->oticket[$j]	+= 1;
					break;
				case 'on-hold'	: $data->ohticket[$j]	+= 1;			
			} 
		}
		return $data;
	}
}
?>