<?php
/**
 * @package        $Id: obhelpdesk.php 102 2013-12-11 02:03:30Z thongta $
 * @author         foobla.com
 * @copyright      2007-2014 foobla.com. All rights reserved.
 * @license        GNU/GPL.
 */

// no direct access
defined( '_JEXEC' ) or die;
defined( 'DS' ) or define( 'DS', DIRECTORY_SEPARATOR );
// Require the helper library
jimport( 'joomla.application.component.controller' );

global $mainframe, $option, $isJ25;

$version = new JVersion();
$obJVer  = $version->getShortVersion();
$isJ25   = substr( $obJVer, 0, 3 ) == '2.5';

JTable::addIncludePath( JPATH_COMPONENT . '/tables' );
require_once JPATH_COMPONENT . DS . 'helpers' . DS . 'obhelpdesk.php';
require_once JPATH_COMPONENT . DS . 'helpers' . DS . 'user.php';
require_once JPATH_COMPONENT . DS . 'helpers' . DS . 'ticket.php';
require_once JPATH_COMPONENT . DS . 'helpers' . DS . 'fields.php';
require_once JPATH_COMPONENT . DS . 'helpers' . DS . 'cronMail.php';

$option = 'com_obhelpdesk';
// add css  Js of bootstrap
$document = JFactory::getDocument();
// $document->addStyleSheet('components/'.$option.'/assets/obstyle.css');
if ( $isJ25 ) {
	jimport( 'joomla.application.component.controller' );
	jimport( 'joomla.application.component.model' );
	jimport( 'joomla.application.component.view' );

	class obController extends JController {
	}

	class obModel extends JModel {
	}

	class obView extends JView {
	}
} else {
	class obController extends JControllerLegacy {
	}

	class obModel extends JModelLegacy {
	}

	class obView extends JViewLegacy {
	}
}

// load JUI stylesheet & javascript
$document->addStyleSheet( 'components/' . $option . '/assets/jui/css/bootstrap.min.css' );
$document->addStyleSheet( 'components/' . $option . '/assets/jui/css/bootstrap-extended.min.css' );
$document->addStyleSheet( 'components/' . $option . '/assets/jui/css/icomoon.min.css' );
// 	$document->addStyleSheet('components/'.$option.'/assets/jui/css/chosen.css');
if ( $isJ25 ) {
	$document->addScript( 'components/' . $option . '/assets/jui/js/jquery.min.js' );
	$document->addScript( 'components/' . $option . '/assets/jui/js/jquery-noconflict.js' );
	$document->addScript( 'components/' . $option . '/assets/jui/js/chosen.jquery.min.js' );
	$document->addScript( 'components/' . $option . '/assets/jui/js/bootstrap.min.js' );
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
	$document->addScriptDeclaration( $script );
}
// $document->addStyleSheet(JURI::base().'components/'.$option.'/assets/css/bootstrap.css');
$document->addStyleSheet( JURI::base() . 'components/' . $option . '/assets/css/style.min.css' );

$controller = obController::getInstance( 'obHelpDesk' );
if ( $isJ25 ) {
	$controller->execute( JRequest::getCmd( 'task' ) );
} else {
	$controller->execute( JFactory::getApplication()->input->get( 'task' ) );
}

$controller->redirect();