<?php
/**
* @package		$Id: reports.php 23 2013-08-15 10:15:43Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class obHelpDeskControllerReports extends obController{
	function __construct(){
		parent::__construct();
		$this->registerTask( 'export', 				'exportTickets');
	}

	function display($cachable = false, $urlparams = false)
	{
		$document 	= JFactory::getDocument();
		$vType 		= $document->getType();
		$vName 		= JRequest::getVar('view', 'reports');
		$vLayout	= JRequest::getVar('report', 'default');
		$view = $this->getView($vName, $vType);
		$view->setLayout($vLayout);

		$mName 	= 'reports';
		if ($model 	= $this->getModel($mName)) {
			$view->setModel($model, true);
		}

		$view->display();
	}

	function export(){
		$filter_department	= JRequest::getVar('department_id');
		$filter_staff		= JRequest::getVar('staff_id');
		$filter_from		= JRequest::getVar('filter_from','');
		$filter_to			= JRequest::getVar('filter_to','');
		$type 				= JRequest::getVar('export_type', 'excel');
		if($type == 'excel') $ext = 'xls';
		else $ext = $type;
		$where = array();
		if(count($filter_department) && $filter_department[0]) {
			$imp_dep = implode(',', $filter_department);
			$where[] = " i.department_id IN (".$imp_dep.")";
		}
		if(count($filter_staff) && $filter_staff[0]) {
			$imp_staff = implode(',', $filter_staff);
			$where[] = " i.user_id IN (".$imp_staff.")";
		}
		if($filter_from) {
			$from = JFactory::getDate("$filter_from")->toSql();
			$where[] = "i.created >= '".$from."'";
		}
		if($filter_staff) {
			$to = JFactory::getDate("$filter_from")->toSql();
			$where[] = "i.created <= '".$to."'";
		}

		$str_where = implode(' AND ', $where);
		$db = JFactory::getDbo();
		$query = " SELECT i.*, u.name as name, us.name as usname, CONCAT_WS('-', d.`prefix`, i.`id`) as dcode, d.title as dname"
 			 ." FROM `#__obhelpdesk3_tickets` AS i "
			 ." LEFT JOIN `#__users` as u ON i.customer_id = u.id"
			 ." LEFT JOIN `#__users` as us ON i.staff = us.id"
			 ." LEFT JOIN `#__obhelpdesk3_departments` as d ON i.departmentid = d.id"
			 ." WHERE ". $str_where
		;
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$content = "ID"."	"."[Code]Subject"."	"."Customer"."	"."Staff"."	"."Department"."	"
					."Spent Time (min)"."	"."Priority"."	"."Status"."	"."Submitted Date"."\n";
		foreach ($rows as $row) {
/* 			$query_spent_time = "SELECT SUM(ticket_time) as spent_time FROM `#__obhelpdesk3_messages` WHERE `ticket_id`=".$row->id;
			$db->setQuery($query_spent_time);
			$sum = $db->loadResult(); */
			$content .= $row->id."	";
			$content .= "[".$row->dcode."] ".$row->subject."	";
			$content .= $row->usname."	";
			$content .= $row->name."	";
			$content .= $row->dname."	";
// 			$content .= $sum."	";
			$content .= $row->priority."	";
			$content .= $row->status."	";
			$content .= JFactory::getDate($row->created)->format('Y-m-d')."	";
			$content .= "\n";
		}
		$content = str_replace("\r", "", $content);
		// do export tickets
		$mimeType ='text/x-'.$ext;
		$filename = 'reports_'.JFactory::getDate('now')->format('Ymd').'.'.$ext;
		self::CreateDownloadFile($content, $filename, $mimeType);
	}

	public function CreateDownloadFile( $text, $filename , $mimeType ) {
		if ( ini_get( 'zlib.output_compression' ) )
		{
			/* Required for IE. Content-Disposition may get ignored otherwise. */
			ini_set( 'zlib.output_compression', 'Off' );
		}
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Transfer-Encoding: UTF-8' );
		header( 'Content-Type: ' . $mimeType );
		/* Make the download non-cacheable. */
		header( 'Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT' );
		header( 'Cache-control: private' );
		header( 'Pragma: private' );

		echo $text;
		flush();
		$app = JFactory::getApplication();
		$app->close();
	}
}