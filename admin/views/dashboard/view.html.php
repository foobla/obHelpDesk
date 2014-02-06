<?php
/**
* @package		$Id: view.html.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
jimport('joomla.html.pane');

class obHelpDeskViewDashboard extends obView
{
	function display($tpl = null)
	{
		JHTML::stylesheet( 'style.css', 'administrator/components/com_obhelpdesk/assets/' );
		JToolBarHelper::title( JText::_('OBHELPDESK_CPANEL_OBHELPDESK'), 'obhelpdesk.png' );
//		JToolBarHelper::help('about', true);
		
// 		echo '
// 			<div id="foobla">
// 				<div class="row-fluid">
// 		';
// 		$this->loadQuickIcons();
// 		echo "
// 				</div>
// 			</div>
// 		";
		
		$this->addToolbar();
		// display
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		JToolBarHelper::preferences('com_obhelpdesk', 500, 800, 'JOPTIONS');
	}
	
	
	/**
	 * load QuickIcons
	 *
	 */
	function loadQuickIcons()
	{
		echo '
			<ul class="thumbnails">
		';
		# first row
		/*$this->quickiconButton('index.php?option=com_obhelpdesk&view=config',			'configuration_48.png', JText::_('OBHELPDESK_CPANEL_CONFIGURATION'));
		$this->quickiconButton('index.php?option=com_obhelpdesk&view=upgrade',			'icon_support_48.png', 			JText::_('OBHELPDESK_CPANEL_SUPPORT'));
		$this->quickiconButton('index.php?option=com_obhelpdesk&view=priorities',			'priority_32.png', 			JText::_('OBHELPDESK_DEPARTMENT_PRIORITY'));
		$this->quickiconButton('index.php?option=com_obhelpdesk&view=reports',			'reports_48.png', 			JText::_('OBHELPDESK_CPANEL_REPORTS'));
		*/
		# 2nd row
		$this->quickiconButton('index.php?option=com_obhelpdesk&view=departments',		'icon-48-departments.png', 		JText::_('OBHELPDESK_CPANEL_DEPARTMENTS'));
		$this->quickiconButton('index.php?option=com_obhelpdesk&view=groups',			'icon-48-groups.png', 		JText::_('OBHELPDESK_CPANEL_GROUPS'));
		$this->quickiconButton('index.php?option=com_obhelpdesk&view=staffs',				'icon-48-staffs.png', 		JText::_('OBHELPDESK_CPANEL_STAFFS'));
		$this->quickiconButton('index.php?option=com_obhelpdesk&view=fields',			'icon-48-custom-fields.png', 		JText::_('OBHELPDESK_CPANEL_CUSTOM_FIELDS'));
		$this->quickiconButton('index.php?option=com_obhelpdesk&view=replytemplates',	'icon-48-reply-template.png', 		JText::_('OBHELPDESK_CPANEL_REPLY_TEMPLATES'));
	    # 3rd row
		$this->quickiconButton('index.php?option=com_obhelpdesk&task=department.add',		'icon-48-departments-new.png', 		JText::_('OBHELPDESK_CPANEL_NEW_DEPARTMENT'));
		$this->quickiconButton('index.php?option=com_obhelpdesk&task=group.add',			'icon-48-groups-new.png', 		JText::_('OBHELPDESK_CPANEL_NEW_GROUP'));
		$this->quickiconButton('index.php?option=com_obhelpdesk&task=staff.add',				'icon-48-staffs-new.png', 		JText::_('OBHELPDESK_CPANEL_NEW_STAFF'));
		$this->quickiconButton('index.php?option=com_obhelpdesk&task=field.add',			'icon-48-custom-fields-new.png', 		JText::_('OBHELPDESK_CPANEL_NEW_CUSTOMFIELD'));
		$this->quickiconButton('index.php?option=com_obhelpdesk&task=replytemplate.add',	'icon-48-reply-template-new.png', 		JText::_('OBHELPDESK_CPANEL_NEW_REPLYTEMPLATE'));
		# other rows
		
		$this->quickiconButton('index.php?option=com_obhelpdesk&view=priorities',			'priority_32.png', 			JText::_('OBHELPDESK_DEPARTMENT_PRIORITIES'));
		$this->quickiconButton('index.php?option=com_obhelpdesk&view=reports',			'reports_48.png', 			JText::_('OBHELPDESK_CPANEL_REPORTS'));
		echo '
			</ul>
		';
	}
	
	/**
	 * load a Quick Icon
	 *
	 * @param unknown_type $link
	 * @param unknown_type $image
	 * @param unknown_type $text
	 */
	function quickiconButton($link, $image, $text)
	{
		$lang = JFactory::getLanguage();
		?>
		<div class="span2">
			<div class="icon">
				<a href="<?php echo $link; ?>">
					<?php  
					$src = JURI::base().'components/com_obhelpdesk/assets/images/icons/'. $image;
					$title = $text;
					echo '<img src="'.$src.'" title="'.$title.'"/>';
					?>
					<span><?php echo $text; ?></span>
				</a>
			</div>
		</div>
		<?php
	} // end quickiconButton

} // end class
?>