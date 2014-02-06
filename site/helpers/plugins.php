<?php
/**
* @package		$Id: plugins.php 2 2013-07-30 08:16:00Z thongta $
* @author 		Thong Tran - foobla.com
* @copyright	Copyright (C) 2007-2011 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.html.parameter');

class obHelpDeskPluginHelper
{
	public static function isShow($plg_name, $row)
	{
		# get configured permission

		$plugin 			= JPluginHelper::getPlugin('obhelpdesk', strtolower($plg_name));
		$params 			= new JRegistry( $plugin->params );
		$who_permission 	= $params->get('who_permission', 2);
		$where_permission	= $params->get('where_permission', 1);
// 		$poster_id 			= $row->customer_id;
		
		# current logged in user
		$user = JFactory::getUser();
		
		include_once(JPATH_SITE.DS.'components'.DS.'com_obhelpdesk'.DS.'helpers'.DS.'user.php');
		$is_staff = obHelpDeskUserHelper::is_staff($user->id);
		# check who: 0=>both, 1=>customer, 2=>staff
		if ($who_permission == 0) { # 0: both can see
			#return TRUE;
		} elseif ($who_permission == 1) { # 1: only customer can see
			if($is_staff) {
				return FALSE;
			} else {
				#return TRUE;
			}
		} else { # 2: only staff can see
			if($is_staff) {
				#return TRUE;
			} else {
				return FALSE;
			}
		}
		
		# check where: 0=>both, 1=>customer, 2=>staff
		if ($where_permission == 0) { # 0: display on both
			return TRUE;
		} elseif ($where_permission == 1) { # 1: display on customer reply only
			if($is_staff) {
				return FALSE;
			} else {
				return TRUE;
			}
		} else { # 2: display on staff reply only
			if($is_staff) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}
}
?>