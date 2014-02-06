<?php
/**
* @package		$Id: edit_fields.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

$s_fields = array();
//get selected fields
if($this->item->fields) $s_fields = explode(',', $this->item->fields); 
?>
<table class="table table-striped">
	<thead>
	<tr>
		<th width="1%" align="center">
			<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this, 'field')" />
		</th>
		<th><?php echo JText::_('OBHELPDESK_FIELDS_FIELD_NAME');?></th>
		<th><?php echo JText::_('OBHELPDESK_FIELD_TYPE');?></th>
		<th><?php echo JText::_('OBHELPDESK_FIELDS_REQUIRED');?></th>
	</tr>
	</thead>
	<?php $i = 0;?>
	<?php foreach ($this->fields as $field):?>
	<?php if($field->published == 1):?>
	<?php $check = ''; ?>
	<?php if(in_array($field->id, $s_fields)) $check = 'checked="checked"'; ?>
	<tr>
		<td>
		<input type="checkbox" <?php echo $check;?> value="<?php echo $field->id;?>" name="jform[fields][]" id="field<?php echo $i?>">
		</td>
		<td><?php echo $field->title;?></td>
		<td><?php echo $this->options[$field->type];?></td>
		<td>
		<?php if($field->required == 1):?>
		<?php echo '<span style="color:red">'.JText::_('JYES').'</span>'; ?>
		<?php else:?>
		<?php echo '<span style="color:#025A8D">'.JText::_('JNO').'</span>'?>
		<?php endif;?>
		</td>
	</tr>
	<?php endif;?>
	<?php endforeach;?>
</table>