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
		if (task == 'replytemplate.cancel' || document.formvalidator.isValid(document.id('replytemplate-form'))) {
			Joomla.submitform(task, document.getElementById('replytemplate-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<div id="foobla">
<form action="<?php echo JRoute::_('index.php?option=com_obhelpdesk&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="replytemplate-form" class="form-validate">
	<div class="row-fluid">
	<div class="span8">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'OBHELPDESK_DETAILS' ); ?></legend>
			<?php obHelpDeskHelper::loadFieldset($this->form, 'details');?>
		</fieldset>
	</div>
	
	<div class="span4">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_OBHELPDESK_KEYWORD_MEANING' ); ?></legend>
			<table class="table table-striped">
				<thead>
					<tr>
						<th> 
							<?php echo JText::_( 'COM_OBHELPDESK_KEYWORDS' ); ?>
						</th>
						<th>
							<?php echo JText::_( 'COM_OBHELPDESK_MEANING' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php 
				$keys = array_keys($this->templatekey); 
				$values = array_values($this->templatekey);
				foreach ($keys as $k => $v) :?>
					<tr>
						<td>
							<?php echo $v; ?>
						</td>
						<td>
							<?php echo $values[$k]; ?>
						</td>
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
		</fieldset>
	</div>
	</div>
	<input type="hidden" name="task" value="replytemplate.edit" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>