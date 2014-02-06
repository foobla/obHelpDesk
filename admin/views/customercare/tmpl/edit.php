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
		if (task == 'customercare.cancel' || document.formvalidator.isValid(document.id('customercare-form'))) {
			Joomla.submitform(task, document.getElementById('customercare-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<div id="foobla">

<form action="<?php echo JRoute::_('index.php?option=com_obhelpdesk&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="customercare-form" class="form-validate form-horizontal">
	<div class="row-fluid">
		<div class="span6">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'OBHELPDESK_CUSTOMER_CARE_WHO' ); ?></legend>
				<?php obHelpDeskHelper::loadFieldset($this->form, 'details');?>
			</fieldset>
		</div>
		<div class="span6">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'OBHELPDESK_CUSTOMERCARE_OPTIONS' ); ?></legend>
				<?php obHelpDeskHelper::loadFieldset($this->form, 'staff');?>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="customercare.edit" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>