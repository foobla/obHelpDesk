<?php
/**
* @package		$Id: charts.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

interface SA_Chart {

	public function getPackage();
	public function getChartDataOutput();

}

?>