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
		if (task == 'group.cancel' || document.formvalidator.isValid(document.id('group-form'))) {
			Joomla.submitform(task, document.getElementById('group-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}

	jQuery(document).ready(function ($){
		$('#myTab a').click(function (e)
		{
			e.preventDefault();
			$(this).tab('show');
		});
	});
</script>

<div id="foobla">

<form action="<?php echo JRoute::_('index.php?option=com_obhelpdesk&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="group-form" class="form-validate form-horizontal">
	<div class="">
		<h3><?php echo JText::_( 'OBHELPDESK_DETAILS' ); ?></h3>
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
	</div>
	
	<ul class="nav nav-tabs" id="myTab">
		<li class="active"><a href="#basic"><?php echo JText::_( 'OBHELPDESK_GROUPS_BASIC_PERMISSIONS' ); ?></a></li>
		<li><a href="#advance"><?php echo JText::_('OBHELPDESK_GROUPS_ADVANCE_PERMISSIONS') ?></a></li>
	</ul>
	
	<div class="tab-content">
	
		<div class="tab-pane active" id="basic">
			<?php foreach($this->form->getFieldset('basic') as $field): ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input;?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		
		<div class="tab-pane" id="advance">
			<?php foreach($this->form->getFieldset('advance') as $field): ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input;?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<input type="hidden" name="task" value="group.edit" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>