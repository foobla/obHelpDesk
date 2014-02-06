<?php
/**
* @package		$Id: obhelpdesk.php 63 2013-09-06 03:01:01Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;
defined('DS') or define('DS',DIRECTORY_SEPARATOR);
$version = new JVersion();
$obJVer  = $version->getShortVersion();
$isJ25 = substr($obJVer, 0,3)=="2.5";

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_obhelpdesk'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependencies
jimport('joomla.application.component.controller');

$document = JFactory::getDocument();
$document->addStyleSheet('components/com_obhelpdesk/assets/style.css');
if ($isJ25) {
	jimport( 'joomla.application.component.controller' );
	jimport('joomla.application.component.model');
	jimport( 'joomla.application.component.view');

	class obController	extends JController {}
	class obModel		extends JModel {}
	class obView		extends JView {}
}else{
	class obController	extends JControllerLegacy {}
	class obModel		extends JModelLegacy {}
	class obView		extends JViewLegacy {}
}

if ($isJ25) {
	$document->addStyleSheet('../components/com_obhelpdesk/assets/jui/css/bootstrap.css');
	$document->addStyleSheet('../components/com_obhelpdesk/assets/jui/css/bootstrap-extended.css');
	$document->addStyleSheet('../components/com_obhelpdesk/assets/jui/css/icomoon.css');
	//$document->addStyleSheet('components/com_obgrabber/assets/jui/css/chosen.css');
	$document->addScript('../components/com_obhelpdesk/assets/jui/js/jquery.min.js');
	$document->addScript('../components/com_obhelpdesk/assets/jui/js/jquery-noconflict.js');
	$document->addScript('../components/com_obhelpdesk/assets/jui/js/chosen.jquery.min.js');
	$document->addScript('../components/com_obhelpdesk/assets/jui/js/bootstrap.min.js');

	$script = "
		// Bootstrap nav-tabs
		jQuery(document).ready(function ($){
			(function($){

				// Turn radios into btn-group
				$('.radio.btn-group label').addClass('btn');
				$('.btn-group label:not(.active)').click(function() {
					var label = $(this);
					var input = $('#' + label.attr('for'));

					if (!input.prop('checked')) {
						label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');
						if(input.val()== '') {
								label.addClass('active btn-primary');
						 } else if(input.val()==0) {
								label.addClass('active btn-danger');
						 } else {
						label.addClass('active btn-success');
						 }
						input.prop('checked', true);
					}
				});
				$('.btn-group input[checked=checked]').each(function() {
					if($(this).val()== '') {
					   $('label[for=' + $(this).attr('id') + ']').addClass('active btn-primary');
					} else if($(this).val()==0) {
					   $('label[for=' + $(this).attr('id') + ']').addClass('active btn-danger');
					} else {
						$('label[for=' + $(this).attr('id') + ']').addClass('active btn-success');
					}
				});
			})(jQuery);
		});";
	$document->addScriptDeclaration($script);
}


$controller = obController::getInstance('obHelpDesk');
$task = JRequest::getVar('task');
if($isJ25){
	$controller->execute($task);
}else{
	$controller->execute(JFactory::getApplication()->input->get('task'));
}
$controller->redirect();