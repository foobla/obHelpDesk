<?php
/**
* @package		$Id: default.php 25 2013-08-15 14:57:24Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

?>
<div id="foobla">
	<div class="row-fluid">
		<div class="span8">
			<!-- icons -->
			<ul class="thumbnails">
				<li class="span3" style="margin-left: 15px;">
					<div class="thumbnail">
						<a href="index.php?option=com_obhelpdesk&view=departments">
						<img src="components/com_obhelpdesk/assets/images/icons/icon-48-departments.png" alt="">
						<h4><?php echo JText::_('OBHELPDESK_CPANEL_DEPARTMENTS'); ?></h4>
						</a>
					</div>
				</li>
				<li class="span3">
					<div class="thumbnail">
						<a href="index.php?option=com_obhelpdesk&task=department.add">
						<img src="components/com_obhelpdesk/assets/images/icons/icon-48-departments-new.png" alt="">
						<h4><?php echo JText::_('OBHELPDESK_CPANEL_NEW_DEPARTMENT'); ?></h4>
						</a>
					</div>
				</li>
				<li class="span3">
					<div class="thumbnail">
						<a href="index.php?option=com_obhelpdesk&view=groups">
						<img src="components/com_obhelpdesk/assets/images/icons/icon-48-groups.png" alt="">
						<h4><?php echo JText::_('OBHELPDESK_CPANEL_GROUPS'); ?></h4>
						</a>
					</div>
				</li>
				<li class="span3">
					<div class="thumbnail">
						<a href="index.php?option=com_obhelpdesk&task=group.add">
						<img src="components/com_obhelpdesk/assets/images/icons/icon-48-groups-new.png" alt="">
						<h4><?php echo JText::_('OBHELPDESK_CPANEL_NEW_GROUP'); ?></h4>
						</a>
					</div>
				</li>
				<li class="span3">
					<div class="thumbnail">
						<a href="index.php?option=com_obhelpdesk&view=staffs">
						<img src="components/com_obhelpdesk/assets/images/icons/icon-48-staffs.png" alt="">
						<h4><?php echo JText::_('OBHELPDESK_CPANEL_STAFFS'); ?></h4>
						</a>
					</div>
				</li>
				<li class="span3">
					<div class="thumbnail">
						<a href="index.php?option=com_obhelpdesk&task=staff.add">
						<img src="components/com_obhelpdesk/assets/images/icons/icon-48-staffs-new.png" alt="">
						<h4><?php echo JText::_('OBHELPDESK_CPANEL_NEW_STAFF'); ?></h4>
						</a>
					</div>
				</li>
				<li class="span3">
					<div class="thumbnail">
						<a href="index.php?option=com_obhelpdesk&view=fields">
						<img src="components/com_obhelpdesk/assets/images/icons/icon-48-custom-fields.png" alt="">
						<h4><?php echo JText::_('OBHELPDESK_CPANEL_CUSTOM_FIELDS'); ?></h4>
						</a>
					</div>
				</li>
				<li class="span3">
					<div class="thumbnail">
						<a href="index.php?option=com_obhelpdesk&task=field.add">
						<img src="components/com_obhelpdesk/assets/images/icons/icon-48-custom-fields-new.png" alt="">
						<h4><?php echo JText::_('OBHELPDESK_CPANEL_NEW_CUSTOMFIELD'); ?></h4>
						</a>
					</div>
				</li>
				<li class="span3">
					<div class="thumbnail">
						<a href="index.php?option=com_obhelpdesk&view=replytemplates">
						<img src="components/com_obhelpdesk/assets/images/icons/icon-48-reply-template.png" alt="">
						<h4><?php echo JText::_('OBHELPDESK_CPANEL_REPLY_TEMPLATES'); ?></h4>
						</a>
					</div>
				</li>
				
				<li class="span3">
					<div class="thumbnail">
						<a href="index.php?option=com_obhelpdesk&task=replytemplate.add">
						<img src="components/com_obhelpdesk/assets/images/icons/icon-48-reply-template-new.png" alt="">
						<h4><?php echo JText::_('OBHELPDESK_CPANEL_NEW_REPLYTEMPLATE'); ?></h4>
						</a>
					</div>
				</li>
				<li class="span3">
					<div class="thumbnail">
						<a href="index.php?option=com_obhelpdesk&view=priorities">
						<img src="components/com_obhelpdesk/assets/images/icons/priority_32.png" alt="">
						<h4><?php echo JText::_('OBHELPDESK_DEPARTMENT_PRIORITIES'); ?></h4>
						</a>
					</div>
				</li>
				<li class="span3">
					<div class="thumbnail">
						<a href="index.php?option=com_obhelpdesk&view=reports">
						<img src="components/com_obhelpdesk/assets/images/icons/reports_48.png" alt="">
						<h4><?php echo JText::_('OBHELPDESK_CPANEL_REPORTS'); ?></h4>
						</a>
					</div>
				</li>
			</ul>
		</div>
		<div class="span4">
			<!-- credits -->
			<?php echo $this->loadTemplate('info'); ?>
			<?php echo $this->loadTemplate('jed'); ?>
		</div>
	</div>
</div>