<?php
/**
* @package		$Id: view.html.php 23 2013-08-15 10:15:43Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class obHelpDeskViewReports extends obView
{
	function display($tpl = null)
	{
		$option = 'com_obhelpdesk';
		JHTML::stylesheet( 'style.css', 'administrator/components/'.$option.'/assets/' );
		JToolBarHelper::title( JText::_('OBHELPDESK_CPANEL_REPORTS'), 'reports.png' );
		JHTML::stylesheet( 'default.css', 'administrator/components/'.$option.'/assets/css/' );
		JToolBarHelper::custom('reports.report', 'report', 'report', JText::_('Report'), false, false );
		JToolBarHelper::custom('reports.export', 'export', 'export', JText::_('Export'), false, false );

		$option		= JRequest::getVar('option','com_obhelpdesk');
		$controller	= JRequest::getVar('controller','com_obhelpdesk');
		$this->assignRef('controller', $controller);

		parent::display($tpl);
	}

}
