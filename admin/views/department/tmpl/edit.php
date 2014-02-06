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
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'department.cancel' || document.formvalidator.isValid(document.id('department-form'))) {
			Joomla.submitform(task, document.getElementById('department-form'));
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

<form action="<?php echo JRoute::_('index.php?option=com_obhelpdesk&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="department-form" class="form-validate form-horizontal">
	<ul class="nav nav-tabs" id="myTab">
		<li class="active"><a href="#details"><?php echo JText::_( 'OBHELPDESK_DETAILS' ); ?></a></li>
		<li><a href="#customfields"><?php echo JText::_('OBHELPDESK_CUSTOM_FIELDS') ?></a></li>
		<li><a href="#advance"><?php echo JText::_('OBHELPDESK_DEPARTMENT_ADVANCED') ?></a></li>
		<li><a href="#groups"><?php echo JText::_('OBHELPDESK_DEPARTMENT_USERGROUPS') ?></a></li>
	</ul>
	
	<div class="tab-content">
	
		<div class="tab-pane active" id="details">
			<?php obHelpDeskHelper::loadFieldset($this->form, 'details');?>
		</div>
		<div class="tab-pane" id="customfields">
			<?php echo $this->loadTemplate('fields');?>
		</div>
		<div class="tab-pane" id="advance">
			<?php obHelpDeskHelper::loadFieldset($this->form, 'advance');?>
		</div>
		<div class="tab-pane" id="groups">
			<fieldset class="adminform" >
			<?php echo $this->loadTemplate('groups');?>
			</fieldset>
		</div>
		
	</div>
	<div>
		<input type="hidden" name="task" value="department.edit" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
</div>