<?php

// ensure a valid entry point
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');

class obHelpDeskViewDashboard extends obView
{
	function display($tpl = null)
	{
		parent::display();
	}

	function loadPosition( $position, $style=-2 )
	{
		$document	= JFactory::getDocument();
		$renderer	= $document->loadRenderer('module');
		$params		= array('style'=>$style);
	
		$contents = '';
		foreach (JModuleHelper::getModules($position) as $mod)  {
			$contents .= $renderer->render($mod, $params);
		}
		return $contents;
	}
} // end class
?>