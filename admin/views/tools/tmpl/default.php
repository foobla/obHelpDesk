<?php
/**
* @package		$Id: default.php 43 2013-08-30 08:32:49Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;
// JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
// JHtml::_('dropdown.init');
// JHtml::_('formbehavior.chosen', 'select');
// $pane = JPane::getInstance('Tabs');
$option = 'com_obhelpdesk';
$controller = JRequest::getVar('controller', 'tools');
?>
<script>
	Joomla.submitbutton = function( button ){
		form = document.getElementById('adminForm');
		if(button=='tools.import_obhd'){
			var res = confirm('<?php echo JText::_('OBHELPDESK_TOOL_IMPORT_CONFIRM_MSG');?>');
			if( res ){
				form.task.value=button;
				form.submit();
			} else {
				return false;
			}
		}
	}
</script>
<form name="adminForm" action="index.php">
<div class="alert">
	
	<strong><?php echo JText::_('OBHELPDESK_WARNING')?>!</strong> 
	<?php echo JText::_("OBHELPDESK_TOOL_IMPORT_FROM_OBHELPDESK_DESC");?>
</div>
	<input type="hidden" name="option" value="<?php echo $option; ?>"/>
	<input type="hidden" name="option" value="tools"/>
	<input type="hidden" name="task" value="<?php echo $option; ?>"/>
</form>
<br/>
<a onclick="Joomla.submitbutton('tools.import_obhd'); return false;" class="btn btn-large btn-danger" href="index.php?option=<?php echo $option?>&task=tools.import_obhd">
<?php echo JText::_("OBHELPDESK_TOOL_IMPORT_FROM_OBHELPDESK");?>
</a>