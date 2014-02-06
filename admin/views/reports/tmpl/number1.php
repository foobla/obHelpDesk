<?php
/**
* @package		$Id: number1.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

global $mainframe;

$sa_report = $this->getModel('reports');
$sa_report->getTicketCountData();
//var_dump($sa_report);

require_once (JPATH_BASE  .DS. 'components' .DS. 'com_obhelpdesk' .DS. 'views' .DS. 'reports' .DS. 'tmpl' .DS. 'layout.php');
$classname = 'SA_Layout';
$sa_layout = new $classname($sa_report);
$sa_layout->initialise();

$htmlElement_lineChart = 'line_chart_div';
$jsElement_lineChart = 'lineChart_gdata';

$htmlElement_tableChart = 'table_chart_div';
$jsElement_tableChart = 'tableChart_gdata';

$graph_height='430';
$sa_table_page_size='500';

require_once (JPATH_BASE  .DS. 'components' .DS. 'com_obhelpdesk' .DS. 'views' .DS. 'reports' .DS. 'tmpl' .DS. 'graphs' .DS. 'charts.php');
require_once (JPATH_BASE  .DS. 'components' .DS. 'com_obhelpdesk' .DS. 'views' .DS. 'reports' .DS. 'tmpl' .DS. 'graphs' .DS. 'line_chart.php');
require_once (JPATH_BASE  .DS. 'components' .DS. 'com_obhelpdesk' .DS. 'views' .DS. 'reports' .DS. 'tmpl' .DS. 'graphs' .DS. 'table_chart.php');

$classname	= 'SA_LineChart';
@$sa_lineChart = new $classname($sa_report->data, $sa_report->report_parameters->graph_columns,  $jsElement_lineChart, $htmlElement_lineChart,$sa_report->calendar_from,$sa_report->calendar_to,$sa_report->calendar_unit);

$classname	= 'SA_TableChart';
@$sa_tableChart = new $classname($sa_report->data, $sa_report->report_parameters->table_columns, $jsElement_tableChart, $htmlElement_tableChart,$sa_report->calendar_from,$sa_report->calendar_to,$sa_report->calendar_unit,"show");

// Load the scripts
$document =  JFactory::getDocument();
$document->addScript("http://www.google.com/jsapi");
$document->addScriptDeclaration("
	google.load('visualization', '1', {packages:['" . $sa_lineChart->getPackage() . "']});
	google.load('visualization', '1', {packages:['" . $sa_tableChart->getPackage() . "']});

	var chart;
	var table;

	google.setOnLoadCallback(drawChart);

	function drawChart() {
		var cssClassNames = {
			'headerRow': 'table-header',
			'oddTableRow': 'beige-background',
			'selectedTableRow': 'orange-background'};

		var options = {width: " . $sa_layout->graph_width . ", showRowNumber: false, 'page': 'enable', pageSize: " . $sa_table_page_size . ", 'allowHtml': true, 'cssClassNames': cssClassNames};

		var " . $jsElement_lineChart . " = new google.visualization.DataTable();
		var " . $jsElement_tableChart . " = new google.visualization.DataTable();

" . $sa_lineChart->getChartDataOutput() . "

" . $sa_tableChart->getChartDataOutput() . "

		chart = new google.visualization.LineChart(document.getElementById('$htmlElement_lineChart'));
		chart.draw(" . $jsElement_lineChart . ", {'width': " . $sa_layout->graph_width . ", 'height': $graph_height, 'legend': 'bottom', vAxis: {minValue:0}});

		table = new google.visualization.Table(document.getElementById('$htmlElement_tableChart'));
		table.draw(" . $jsElement_tableChart . ", options);

		google.visualization.events.addListener(table, 'select', function() {
			var selection = chart.getSelection();
			chart.setSelection([{row: selection[0].row, column:1}]);
			});

		google.visualization.events.addListener(chart, 'select', function(event) {
			var selection = chart.getSelection();
			table.setSelection([{row: selection[0].row, column:null}]);
			});

		google.visualization.events.addListener(chart, 'onmouseover', function(event) {
			  table.setSelection([{row: event['row'], column:null}]);
			});

		google.visualization.events.addListener(chart, 'onmouseout', function(event) {
			  //table.setSelection();
			});
	}

");

$sa_layout->buildLayoutHtmlHeader();
$sa_layout->openHTMLFramework();
$sa_layout->outputSideBarHTML();
$sa_layout->selectReportChart();
$sa_layout->outputTimeframeHTML();
$sa_layout->outputHeaderHTML();
$sa_layout->HTMLReportOrExport();

$sa_layout->showChart($htmlElement_lineChart, $htmlElement_tableChart);
$sa_layout->outputFooterHTML();
$sa_layout->closeHTMLFramework();
?>
