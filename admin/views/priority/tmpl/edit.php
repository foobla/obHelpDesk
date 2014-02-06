<?php
/**
* @package		$Id: edit.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'priority.cancel' || document.formvalidator.isValid(document.id('priority-form'))) {
			Joomla.submitform(task, document.getElementById('priority-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<div id="foobla">
<form action="<?php echo JRoute::_('index.php?option=com_obhelpdesk&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="priority-form" class="form-validate">
 
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'OBHELPDESK_DETAILS' ); ?></legend>
			<?php obHelpDeskHelper::loadFieldset($this->form, 'details');?>
		</fieldset>
	<input type="hidden" name="task" value="priority.edit" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>