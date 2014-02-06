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
		if (task == 'staff.cancel' || document.formvalidator.isValid(document.id('staff-form'))) {
			Joomla.submitform(task, document.getElementById('staff-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<div id="foobla">

<form action="<?php echo JRoute::_('index.php?option=com_obhelpdesk&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="staff-form" class="form-validate form-horizontal">
 
	<ul class="adminformlist">
	<?php foreach($this->form->getFieldset('details') as $field): ?>
	<div class="control-group">
		<div class="control-label">
			<?php echo $field->label; ?>
		</div>
		<div class="controls">
			<?php echo $field->input;?>
		</div>
	</div>
	<?php endforeach; ?>
	</ul>
	
	<input type="hidden" name="task" value="staff.edit" />
	<?php echo JHtml::_('form.token'); ?>
</form>