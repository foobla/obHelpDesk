<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
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
		$app 	= JFactory::getApplication();
		$user 	= JFactory::getUser();
// 		echo '<pre>'.print_r($_REQUEST, true).'</pre>';
// 		exit();
		$view		= JRequest::getCmd('view', 'dashboard');
		$layout 	= JRequest::getCmd('layout', 'default');
		$id			= JRequest::getInt('id');

		if ( !$user->id && $view == 'dashboard' ) {
			$juri 	= JURI::getInstance();
			$return = base64_encode( $juri->toString() );
			$app->redirect('index.php?option=com_users&view=login&Itemid=141&return='.$return);
		}
		elseif($view == 'ticket' && $layout=='reply'){
			$juri 	= JURI::getInstance();
			$return = base64_encode( $juri->toString() );
			$app->redirect('index.php?option=com_obhelpdesk&view=tickets&layout=entercode&Itemid=141&return='.$return);
		}

		parent::display();
	}
}
