<?php 
defined('_JEXEC') or die;

/**
 * @subpackage	com_obhelpdesk
 */
class obHelpDeskMenuHelper 
{
	public function topnav($active_menu) {
		$user = JFactory::getUser();
		?>
			<ul class="nav nav-tabs">
				<?php if ($user->id) : ?>
				<li<?php echo ($active_menu=='dashboard')?' class="active"':'' ?>><a href="<?php echo JRoute::_('index.php?option=com_obhelpdesk&view=dashboard');?>"><?php echo JText::_('COM_OBHELPDESK_DASHBOARD');?></a></li>
				<li<?php echo ($active_menu=='tickets')?' class="active"':'' ?>><a href="<?php echo JRoute::_('index.php?option=com_obhelpdesk&view=tickets');?>"><?php echo JText::_('COM_OBHELPDESK_TICKETS');?></a></li>
				<?php endif; ?>
				<?php if(obHelpDeskUserHelper::is_staff($user->id)):?>
				<li<?php echo ($active_menu=='replytemplates')?' class="active hidden-xs"':' class="hidden-xs"' ?>><a href="<?php echo JRoute::_('index.php?option=com_obhelpdesk&view=replytemplates');?>"><?php echo JText::_('COM_OBHELPDESK_REPLY_TEMPLATES');?></a></li>
				<?php endif;?>
				<li<?php echo ($active_menu=='newticket')?' class="active"':'' ?>>
					<a href="<?php echo JRoute::_('index.php?option=com_obhelpdesk&view=ticket&task=newticket');?>" class="hidden-xs">
						<?php echo JText::_('COM_OBHELPDESK_NEWTICKET');?>
					</a>
					<a href="<?php echo JRoute::_('index.php?option=com_obhelpdesk&view=ticket&task=newticket');?>" class="visible-xs">
						<i class="fa fa-plus-circle"></i>
					</a>
				</li>
			</ul>
		<?php 
	}
	
	public function getCurrentUrl() {
	}
}
?>