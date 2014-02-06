<?php
/**
* @package		$Id: default.php 64 2013-09-06 03:05:57Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('behavior.modal');
?>
<div id="foobla">
<form action="<?php echo JRoute::_('index.php?option=com_obhelpdesk&view=emailtemplates');?>" method="post" name="adminForm" id="adminForm">
	<table class="table table-striped">
		<thead>
			<tr>
				<th width="5">#</th>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th width="30%">
					<?php echo JText::_('COM_OBHELPDESK_SUBJECT');?>
				</th>
				<th>
					<?php echo JText::_('OBHELPDESK_FIELD_TYPE');?>
				</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<tr class="row<?php echo $i % 2; ?>">
					<td>
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td>
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_obhelpdesk&task=emailtemplate.edit&id='.(int) $item->id); ?>">
							<?php if($item->subject == '') echo JText::_('COM_OBHELPDESK_NO_TITLE'); else echo $this->escape($item->subject); ?>
						</a>
					</td>
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_obhelpdesk&task=emailtemplate.edit&id='.(int) $item->id); ?>">
						<?php echo JText::_(strtoupper('COM_OBHELPDESK_'.$item->type));?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>