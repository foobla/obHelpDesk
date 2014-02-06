<?php 
// No direct access.
defined('_JEXEC') or die;

JHTML::_('behavior.mootools');
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

			if(document.getElementById('jform_new_password').value.length < 8) {
				alert('<?php echo $this->escape(JText::_('COM_OBHELPDESK_TICKET_CODE_8CHARACTERS'));?>');
				document.getElementById('jform_new_password').setAttribute("class", 'input-xlarge span4 requried invalid');
				document.getElementById('jform_new_password_lbl').setAttribute("class", 'invalid');
				return false;
			} else{
				document.getElementById('jform_new_password').setAttribute("class", 'input-xlarge span4 requried');
				document.getElementById('jform_new_password_lbl').setAttribute("class", '');
			}

			if(document.getElementById('jform_new_password2').value.length < 8) {
				alert('<?php echo $this->escape(JText::_('COM_OBHELPDESK_TICKET_CODE_8CHARACTERS'));?>');
				document.getElementById('jform_new_password2').setAttribute("class", 'input-xlarge span4 requried invalid');
				document.getElementById('jform_new_password2_lbl').setAttribute("class", 'invalid');
				return false;
			} else{
				document.getElementById('jform_new_password2').setAttribute("class", 'input-xlarge span4 requried');
				document.getElementById('jform_new_password2_lbl').setAttribute("class", '');
			}

			if(document.getElementById('jform_new_password').value != document.getElementById('jform_new_password2').value){
				alert('<?php echo $this->escape(JText::_('COM_OBHELPDESK_MSG_NOT_MATCH_CODE'));?>');
				return false;
			}
			Joomla.submitform(task);
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
			return false;
		}
	}
</script>
<div id="obhelpdesk">
<div class="row-fluid">
	<div class="well">
		<label for="note_required_fields"><i class="icon-info-sign"></i>&nbsp;<?php echo JText::_('COM_OBHELPDESK_ENTER_MAIL_CODE'); ?></label>
	</div>
	<form action="<?php echo JRoute::_('index.php?option=com_obhelpdesk'); ?>" method="post" name="adminForm" id="adminForm" class="well form-validate">
		<label for="jform_email" id="jform_email_lbl"><?php echo $this->escape(JText::_('COM_OBHELPDESK_EMAIL_ADDRESS'));?></label>
		<input type="text" class="input-xlarge required span4 validate-email" id="jform_email" name="jform[email]" />
		<label for="jform_password" id="jform_password_lbl"><?php echo $this->escape(JText::_('COM_OBHELPDESK_OLD_CODE'));?></label>
		<input type="password" class="input-xlarge required span4" id="jform_password" name="jform[password]" />
		<label for="jform_new_password" id="jform_new_password_lbl"><?php echo $this->escape(JText::_('COM_OBHELPDESK_NEW_CODE'));?></label>
		<input type="password" class="input-xlarge required span4" id="jform_new_password" name="jform[new_password]" />
		<label for="jform_new_password2" id="jform_new_password2_lbl"><?php echo $this->escape(JText::_('COM_OBHELPDESK_RETYPE_NEW_CODE'));?></label>
		<input type="password" class="input-xlarge required span4" id="jform_new_password2" name="jform[new_password2]" />
		<button type="submit" class="btn btn-primary" onClick="return Joomla.submitbutton('tickets.changecode')"><?php echo $this->escape(JText::_('COM_OBHELPDESK_SUBMIT'));?></button>
		<div>
			<input type="hidden" name="task" value="tickets.changecode" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
</div>