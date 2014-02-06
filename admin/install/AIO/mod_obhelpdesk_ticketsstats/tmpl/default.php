<?php
/**
* @package		$Id: default.php 30 2013-08-17 04:20:51Z phonglq $
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
          ['Month', '<?php echo JText::_('MOD_OBHELPDESK_TICKETSSTATS_OPEN');?>', '<?php echo JText::_('MOD_OBHELPDESK_TICKETSSTATS_CLOSED');?>', '<?php echo JText::_('MOD_OBHELPDESK_TICKETSSTATS_ON_HOLD');?>' ],
          <?php for($i = 0; $i < count($data->months); $i++):?>
          ['<?php echo $data->months[$i];?>', <?php echo $data->oticket[$i];?>, <?php echo $data->cticket[$i];?>, <?php echo $data->ohticket[$i];?> ],
          <?php endfor;?>
        ]);

        var options = {
          title: '<?php echo JText::_('OBHELPDESK_TICKETS_STATISTIC');?>',
          hAxis: {title: 'Month', titleTextStyle: {color: 'red'}}
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div_tickets'));
        chart.draw(data, options);
      }
    </script>
<div id="chart_div_tickets" style="width: 450px; height: 300px;"></div>