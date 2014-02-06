<?php
/**
* @package		$Id: layout.php 23 2013-08-15 10:15:43Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.html.pane');

class SA_Layout {
	
	var $sa_report;
	var $sa_width;
	var $sa_header_width;
	var $sa_header_left;
	var $sa_main_width;
	var $graph_width;
	var $side_column_width;
	var $header_margin;
	var $header_padding;
	var $report_type_name;
	var $report_name;
	
	var $report_type;
	var $calendar_from;
	var $calendar_to;
	var $calendar_unit;
	
	var $list_departments;
	var $list_staffs;
	var $list_customers;
	var $pane;
	
	public function __construct(obHelpDeskModelReports $_sa_report) {
		global $isJ25;
		$this->sa_report = $_sa_report;
		$this->sa_width = '1200';
		$this->sa_main_width ='980';
		
		$this->sa_header_width = '113';
		
		$this->header_margin = '5';
		$this->header_margin = '5';
		$this->sa_header_left = '0';
		
		$this->graph_width = '950';		
		$this->side_column_width = '180';
		
		$this->report_type_name = 'Statistics';
		$this->report_name = 'Number of tickets';
		
		$this->calendar_from = $_sa_report->calendar_from;
		$this->calendar_to = $_sa_report->calendar_to;	
		$this->calendar_unit = $_sa_report->calendar_unit;
		$this->report_type = $_sa_report->report_type;

		$this->list_customers = $_sa_report->list_customers;
		$this->list_staffs = $_sa_report->list_staffs;
		$this->list_departments = $_sa_report->list_departments;

		$params = array();
		$params['allowAllClose'] = true;
		if($isJ25)
			$this->pane = JPane::getInstance('sliders', $params);
	}

	public function initialise() {
	}

	public function buildLayoutHtmlHeader() {
		global $mainframe;
		$document =  JFactory::getDocument();
		$document->addScript(JURI::base().'components'.DS.'com_obhelpdesk'.DS.'assets'.DS.'js'.DS.'report.js');
		$document->addStyleSheet(JURI::base().'components'.DS.'com_obhelpdesk'.DS.'assets'.DS.'css'.DS.'report.css');
		$document->addStyleDeclaration("
		#wrap {
			width:" . $this->sa_width . "px;
		}
		div.sa-header {
			width:" . $this->sa_header_width . "px;
			margin:15px " . $this->header_margin . "px 0px " . $this->header_margin . "px;
			left:" . $this->sa_header_left . "px;
		}
		#main {
			width:" . $this->sa_main_width . "px;
		}
		#sidebar {
			width:" . $this->side_column_width . "px;
		}
		.header-box {
			width:" . ($this->sa_header_width + ($this->header_margin*2)) . "px;
		}
		");
	}
	
	public function openHTMLFramework() {
		echo '<div id="wrap" class="obhelpdesk-report">
			<form action="index.php?option=com_obhelpdesk&view=reports" id="adminForm" name="adminForm" method="post">
			<input type="hidden" name="option" value="com_obhelpdesk"/>
			<input type="hidden" name="controller" value="reports"/>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="from_timeframe" value="1"/>
			<input type="hidden" name="linkparam" />
			<input type="hidden" name="linkvalue" />
			<input type="hidden" name="export" />
			';
		echo $this->writeFormParameters();
		#echo '<div id="header"><h1><span class="sa-title">' . JText::_($this->report_type_name) . ': ' . JText::_($this->report_name) . '</span></h1>' . $this->outputTimeframeHTML() . '</div>';
		echo '<table width="90%" class="obhelpdesk-table-before">';
	}
	
	function HTMLReportOrExport() {
		?>
		<tr>
			<td>
				<label><?php echo JText::_('OBHELPDESK_REPORT_EXPORT_TYPE')?></label>
			</td>
			<td>
				<input type="radio" name="export_type" value="excel" checked="checked"><?php echo JText::_('OBHELPDESK_REPORT_EXCEL');?>
				<input type="radio" name="export_type" value="csv"><?php echo JText::_('OBHELPDESK_REPORT_CSV');?>
			</td>
		</tr>
		<?php 
	}
	
	public function closeHTMLFramework() {
		echo '</table>
		</form>
		</div>
		';
	}
	
	public function writeFormParameters() {
	}
	
	public function outputTimeframeHTML() {	
		echo '<tr>';
		echo 	'<td>';
		echo 		'<label>'.JText::_('OBHELPDESK_REPORT_TIME_RANGE').'</label>';
		echo 	'</td>';
		echo 	'<td>';
		echo 		JText::_('OBHELPDESK_REPORT_FROM') . ': <br />' 
					. JHTML::_( 'calendar',$this->calendar_from,"calendar_from","calendar_from") 
					. '<br/><br />' . JText::_('OBHELPDESK_REPORT_TO') . ': <br />' 
					. JHTML::_( 'calendar',$this->calendar_to,"calendar_to","calendar_to") ;
		echo 	'</td>';
		echo '</tr>';
	}
	
	function RangeByDate() {
		$units = array();
		$units[] = JHTML::_('select.option', 'day', JText::_('OBHELPDESK_REPORT_DAY'));
//		$units[] = JHTML::_('select.option', 'month', JText::_('OBHELPDESK_REPORT_MONTH'));
//		$units[] = JHTML::_('select.option', 'quarter', JText::_('OBHELPDESK_REPORT_QUARTER'));
//		$units[] = JHTML::_('select.option', 'year', JText::_('OBHELPDESK_REPORT_YEAR'));
		$lists_unit = JHTML::_('select.genericlist', $units, 'calendar_unit','','value','text',$this->calendar_unit);
		echo JText::_('OBHELPDESK_REPORT_UNIT').$lists_unit;
	}
	
	function submitButtonReport(){
		echo '  <input type="submit" value="' . JText::_('OBHELPDESK_REPORT_GO') . '"/>';
	}
	function selectReportChart(){
		echo '<tr>';
		echo 	'<td>';
		echo 		'<label>'.JText::_('OBHELPDESK_REPORT_SELECT_REPORT').'</label>';
		echo 	'</td>';
		echo 	'<td>';
					$reports = array();
					$reports[] = JHTML::_('select.option', 'number1', JText::_('OBHELPDESK_REPORT_NUMBER_OF_TICKET_1'));
					$reports[] = JHTML::_('select.option', 'number2', JText::_('OBHELPDESK_REPORT_NUMBER_OF_TICKET_2'));
					$reports[] = JHTML::_('select.option', 'spent1', JText::_('OBHELPDESK_REPORT_SPENT_TIME_1'));
					$reports[] = JHTML::_('select.option', 'spent2', JText::_('OBHELPDESK_REPORT_SPENT_TIME_2'));
			//		$reports[] = JHTML::_('select.option', 'report_5', JText::_('OBHELPDESK_REPORT_5'));
			//		$reports[] = JHTML::_('select.option', 'report_6', JText::_('OBHELPDESK_REPORT_6'));
					$list_report = JHTML::_('select.genericlist', $reports, 'report', '','value','text',$this->report_type);
		echo 		$list_report;
		echo 	'</td>';
		echo '</tr>';
	}
	
	function showChart($line, $table) {
		?>
		<tr>
		<td colspan=2>
			<div id="main" align="center">
				<div id="<?php echo $line; ?>"></div>
				<br/><br/>
				<div id='<?php echo $table; ?>'></div>
				<br/><br/>
				<br/><br/>
			</div>
		</td>
	</tr>
		<?php 
	}
	
	public function outputHeaderHTML() {
		
	}
	
	public function outputSideBarHTML() {
		echo '<tr>';
		echo 	'<td width="270">';
		echo 		'<label>'.JText::_('OBHELPDESK_REPORT_SELECT_DEPARTMENT').'</label>';
		echo 	'</td>';
		echo 	'<td>';
		#list department (multi select)	
		echo 		$this->list_departments;
		echo 	'</td>';
		echo '</tr>';
		echo '<tr>';
		echo 	'<td>';
		echo 		'<label>'.JText::_('OBHELPDESK_REPORT_SELECT_STAFF').'</label>';
		echo 	'</td>';
		echo 	'<td>';
		#list staff(select one, two,...)
		echo 		$this->list_staffs;
		echo 	'</td>';
		echo '</tr>';
	}
	
	public function outputFooterHTML() {

	}
	
}