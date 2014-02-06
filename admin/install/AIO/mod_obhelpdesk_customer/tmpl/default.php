<?php
/**
* @package		$Id: default.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	Copyright (C) 2007-2010 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// ensure a valid entry point
defined('_JEXEC') or die('Restricted Access');

/*

	<li class="span4">
		<div class="thumbnail">
			<img src="<?php echo JURI::root(); ?>/modules/mod_obhelpdesk_customer/assets/images/ticket_48.png" alt="">
			<h3><a href=""><?php echo JText::_('MOD_OBHELPDESK_CUSTOMER_VIEW_TICKET');?></a></h3>
			<p><?php echo JText::_('MOD_OBHELPDESK_CUSTOMER_VIEW_TICKET_DESC');?></p>
		</div>
	</li>
 */
?>
<ul class="thumbnails">
	<li class="span6">
		<div class="thumbnail">
			<img src="<?php echo JURI::root(); ?>/modules/mod_obhelpdesk_customer/assets/images/create-ticket_48.png" alt="">
			<h3><a href="<?php echo JRoute::_('index.php?option=com_obhelpdesk&view=ticket&task=newticket');?>"><?php echo JText::_('COM_OBHELPDESK_NEWTICKET');?></a></h3>
			<p><?php echo JText::_('MOD_OBHELPDESK_CUSTOMER_NEWTICKET_DESC');?></p>
		</div>
	</li>
	<li class="span6">
		<div class="thumbnail">
			<img src="<?php echo JURI::root(); ?>/modules/mod_obhelpdesk_customer/assets/images/tickets_48.png" alt="">
			<h3><a href="<?php echo JRoute::_('index.php?option=com_obhelpdesk&view=tickets&task=list');?>"><?php echo JText::_('COM_OBHELPDESK_MYTICKETS');?></a></h3>
			<p><?php echo JText::_('MOD_OBHELPDESK_CUSTOMER_MYTICKETS_DESC');?></p>
		</div>
	</li>
	<?php if ($params->get('show_kb', 1) && $params->get('kb_link')!=''):?>
	<li class="span6 nomargin">
		<div class="thumbnail">
			<img src="<?php echo JURI::root(); ?>/modules/mod_obhelpdesk_customer/assets/images/kb_48.png" alt="">
			<h3><a href="<?php echo $params->get('kb_link'); ?>"><?php echo JText::_('MOD_OBHELPDESK_CUSTOMER_KB');?></a></h3>
			<p><?php echo JText::_('MOD_OBHELPDESK_CUSTOMER_KB_DESC');?></p>
		</div>
	</li>
	<?php endif; ?>
	<?php if ($params->get('show_orders', 1) && $params->get('orders_link')!=''):?>
	<li class="span6">
		<div class="thumbnail">
			<img src="<?php echo JURI::root(); ?>/modules/mod_obhelpdesk_customer/assets/images/orders_48.png" alt="">
			<h3><a href="<?php echo $params->get('orders_link'); ?>"><?php echo JText::_('MOD_OBHELPDESK_CUSTOMER_ORDERS');?></a></h3>
			<p><?php echo JText::_('MOD_OBHELPDESK_CUSTOMER_ORDERS_DESC');?></p>
		</div>
	</li>
	<?php endif; ?>
</ul>