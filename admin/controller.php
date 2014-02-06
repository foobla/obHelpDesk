<?php
/**
* @package		$Id: controller.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Component Controller
 */
class obHelpDeskController extends obController
{
	/**
	 * @var		string	The default view.
	 * @since	1.6
	 */
	protected $default_view = 'dashboard';
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/obhelpdesk.php';
		
		$view		= JRequest::getCmd('view', 'dashboard');
		$layout 	= JRequest::getCmd('layout', 'default');
		$id			= JRequest::getInt('id');
		if( $layout!='edit' ){
			// Load the submenu.
			obHelpDeskHelper::addSubmenu(JRequest::getCmd('view', 'dashboard'));
		}
		parent::display();

	}
}
