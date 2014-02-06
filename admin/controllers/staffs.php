<?php
/**
* @package		$Id: staffs.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Staffs Controller Class
 */
class obHelpDeskControllerStaffs extends JControllerAdmin
{
	/**
	 * Proxy for getModel
	 * @since	1.6
	 */
	function &getModel($name = 'Staff', $prefix = 'obHelpDeskModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

}
