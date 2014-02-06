<?php
/**
* @package		$Id: default.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	Copyright (C) 2007-2010 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// ensure a valid entry point
defined('_JEXEC') or die('Restricted Access');
?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Task', 'Department'],
          <?php foreach($data as $de):?>
          ['<?php echo $de->name;?>', <?php echo $de->count;?> ],
          <?php endforeach;?>
        ]);

        var options = {
          title: '<?php echo JText::_('OBHELPDESK_DEPARTMENTS_STATISTIC');?>'
        };

        var chart = new google.visualization.PieChart(document.getElementById('chart_div_department'));
        chart.draw(data, options);
      }
    </script>
<div id="chart_div_department" style="width: auto; height: 300px;"></div>
    