<?php 
// No direct access.
defined('_JEXEC') or die;

// JHTML::_('behavior.mootools');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (document.formvalidator.isValid(document.getElementById('adminForm'))) {
			if(document.getElementById('jform_password').value.length < 8) {
				alert('<?php echo $this->escape(JText::_('COM_OBHELPDESK_TICKET_CODE_8CHARACTERS'));?>');
				document.getElementById('jform_password').setAttribute("class", 'input-xlarge span4 requried invalid');
				document.getElementById('jform_password_lbl').setAttribute("class", 'invalid');
				return false;
			} else{
				document.getElementById('jform_password').setAttribute("class", 'input-xlarge span4 requried');
				document.getElementById('jform_password_lbl').setAttribute("class", '');
			}
					
			Joomla.submitform(task);
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
			return false;
		}
	}
</script>
<div id="foobla">
	<div class="row-fluid">
		<form action="<?php echo JRoute::_('index.php?option=com_obhelpdesk'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
			<legend><?php echo JText::_('COM_OBHELPDESK_ENTER_MAIL_CODE'); ?></legend>
	
			<div class="control-group">
				<label class="control-label" for="jform_email"><?php echo $this->escape(JText::_('COM_OBHELPDESK_EMAIL_ADDRESS'));?></label>
				<div class="controls">
					<input type="text" class="input-xlarge required span4 validate-email" id="jform_email" name="jform[email]" placeholder="<?php echo $this->escape(JText::_('COM_OBHELPDESK_EMAIL_ADDRESS'));?>" />
				</div>
			</div>
	
			<div class="control-group">
				<label class="control-label" for="jform_password"><?php echo $this->escape(JText::_('COM_OBHELPDESK_ENTER_CODE'));?></label>
				<div class="controls">
					<input type="password" class="input-xlarge required span4" id="jform_password" name="jform[password]" placeholder="<?php echo $this->escape(JText::_('COM_OBHELPDESK_ENTER_CODE'));?>" />
				</div>
			</div>
	
			<div class="control-group">
				<div class="controls">
					<button type="submit" class="btn btn-primary" onClick="return Joomla.submitbutton('tickets.entercode')"><?php echo $this->escape(JText::_('COM_OBHELPDESK_SUBMIT'));?></button>
				</div>
			</div>
			
			<input type="hidden" name="task" value="tickets.entercode" />
			<?php echo JHtml::_('form.token'); ?>
		</form>
	</div>
</div>