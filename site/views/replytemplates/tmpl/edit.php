<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
$document = JFactory::getDocument();
# set page title
$document->setTitle( JText::_('COM_OBHELPDESK_EDIT_REPLY'));
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'replytemplate.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
			if(task == 'replytemplate.save') {
				body_id = 'ticket_message';
				rte_id = 'rte_'+body_id;
				ifm = document.getElementById(rte_id);
				myeditor = ifm.contentWindow.document;
				doCheck();
				var message = trim(document.getElementById('ticket_message').value);
				// check content
				if( !message )
				{
					document.getElementById('obhelpdesk-ticket-message').setAttribute("class", 'requried invalid');
					document.getElementById('obhelpdesk-ticket-message').setAttribute("aria-invalid", 'true');
					return false;
				} else {
					document.getElementById('obhelpdesk-ticket-message').setAttribute("class", 'required');
					document.getElementById('obhelpdesk-ticket-message').setAttribute("aria-invalid", 'false');
				}
			}
			Joomla.submitform(task);
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}

	function trim(str)
	{
		if(!str || typeof str != 'string')
			return null;
	
		return str.replace(/^[\s]+/,'').replace(/[\s]+$/,'').replace(/[\s]{2,}/,' ');
	}
</script>
<div id="foobla">
	<?php
	require JPATH_COMPONENT.DS.'helpers'.DS.'menu.php';
	$menu = new obHelpDeskMenuHelper();
	$menu->topnav('replytemplates');
	?>
<div class="clearfix">&nbsp;</div>
<form action="<?php echo JRoute::_('index.php?option=com_obhelpdesk');?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<table class="table table-bordered table-striped">
		<tr>
			<td colspan="2">
				<div class="formelm-buttons">
				<button type="button" class="btn btn-small btn-primary" onclick="Joomla.submitbutton('replytemplate.save')">
					<?php echo JText::_('JSAVE') ?>
				</button>
				<button type="button" class="btn btn-small btn-destroy" onclick="Joomla.submitbutton('replytemplate.cancel')">
					<?php echo JText::_('JCANCEL') ?>
				</button>
				</div>
			</td>
		</tr>
		<tr>
			<td>
			<div class="form-inline">
				<?php echo $this->form->getLabel('subject'); ?>
				<?php echo $this->form->getInput('subject', null, $this->item->subject); ?>
			</div>
			</td>
		</tr>
		<tr>
			<td>
			<div class="form-inline">
				<?php echo $this->form->getLabel('enable'); ?>
				<?php echo $this->form->getInput('enable', null, $this->item->enable); ?>
			</div>
			</td>
		</tr>
		<tr>
			<td>
				<div id="obhelpdesk-ticket-message"><?php echo $this->editor_message; ?></div>
			</td>
		</tr>
	</table>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $this->item->id?>" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
</div>
