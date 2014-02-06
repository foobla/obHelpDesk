<?php
/**
* @package		$Id: default.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

global $mainframe;
$controller = JRequest::getVar('controller');
$option	 ='com_obhelpdesk';
$task 		= JRequest::getVar('task');

$sa_report = $this->getModel('reports');

require_once (JPATH_BASE  .DS. 'components' .DS. 'com_obhelpdesk' .DS. 'views' .DS. 'reports' .DS. 'tmpl' .DS. 'layout.php');
$classname = 'SA_Layout';
$sa_layout = new $classname($sa_report);
$sa_layout->initialise();

$sa_layout->buildLayoutHtmlHeader();
$sa_layout->openHTMLFramework();
$sa_layout->outputSideBarHTML();
$sa_layout->selectReportChart();
$sa_layout->outputTimeframeHTML();
$sa_layout->outputHeaderHTML();
$sa_layout->HTMLReportOrExport();
/*
?>
	<div id="main" align="center">
		<?php echo JTEXT::_('OBHELPDESK_SELECT_CONDITION_THEN_PRESS_GO_BUTTON'); ?>
		<br/><br/>
		<br/><br/>
	</div>

<?php
*/

$sa_layout->outputFooterHTML();
$sa_layout->closeHTMLFramework();
?>
<script type="text/javascript">
Joomla.submitbutton = function(pressbutton) {
	var form = document.adminForm;
	Joomla.submitform( pressbutton );
}

function submitbutton(pressbutton) {
	var form = document.adminForm;
	submitform( pressbutton );
}
</script>