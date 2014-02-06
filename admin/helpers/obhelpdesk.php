<?php
/**
* @package		$Id: obhelpdesk.php 31 2013-08-17 04:33:28Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

/**
 * obHelpDesk component helper.
 */
class obHelpDeskHelper
{
	protected static $actions;
	
	public static function addSubmenu($vName)
	{
		$option = JRequest::getVar('option');
		JSubMenuHelper::addEntry(
			JText::_('OBHELPDESK_DASHBOARD_VIEW'),
			'index.php?option=com_obhelpdesk',
			$vName == 'articles'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('OBHELPDESK_CPANEL_DEPARTMENTS'),
			'index.php?option=com_obhelpdesk&view=departments',
			$vName == 'departments'
		);	
		
		JSubMenuHelper::addEntry(
			JText::_('OBHELPDESK_CPANEL_GROUPS'),
			'index.php?option=com_obhelpdesk&view=groups',
			$vName == 'groups'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('OBHELPDESK_CPANEL_STAFFS'),
			'index.php?option=com_obhelpdesk&view=staffs',
			$vName == 'staffs'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('OBHELPDESK_CPANEL_CUSTOM_FIELDS'),
			'index.php?option=com_obhelpdesk&view=fields',
			$vName == 'fields'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('OBHELPDESK_CPANEL_REPLY_TEMPLATES'),
			'index.php?option=com_obhelpdesk&view=replytemplates',
			$vName == 'replytemplates'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('OBHELPDESK_DEPARTMENT_PRIORITIES'),
			'index.php?option=com_obhelpdesk&view=priorities',
			$vName == 'priorities'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('OBHELPDESK_EMAIL_TEMPLATES'),
			'index.php?option=com_obhelpdesk&view=emailtemplates',
			$vName == 'emailtemplates'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('OBHELPDESK_CPANEL_CUSTOMER_CARE'),
			'index.php?option=com_obhelpdesk&view=customercares',
			$vName == 'customercares'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('OBHELPDESK_CPANEL_REPORTS'),
			'index.php?option=com_obhelpdesk&view=reports',
			$vName == 'reports'
		);
			
	}

	/**
	 * Gets a list of the actions that can be performed.
	 */
	public static function getActions($categoryId = 0, $articleId = 0)
	{
		if (empty(self::$actions))
			{
			$user	= JFactory::getUser();
			self::$actions = new JObject;
	
			$actions = array(
				'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
			);
	
			foreach ($actions as $action) {
				self::$actions->set($action,	$user->authorise($action, 'com_obhelpdesk'));
			}
		}
		
		return self::$actions;
	}
	
	/**
	 * get staff list from string userids determine by comma 
	 */
	
	public static function getStaffList($str) {
		$re_str = '';
		if($str) {
			$uids = explode(',',  $str);
			foreach ($uids as $uid) {
				$staff = JFactory::getUser($uid);
				$re_str .= $staff->username.' ['. $staff->name.']<br />';
			}
		}
		
		return $re_str;
	}
	
	public static function generateQuickCode($datetime) {
// 		$time = JFactory::getDate($datetime)->toFormat('%Y%m%d%H%M%S');
		$time = JFactory::getDate($datetime)->format('YmdHMS');
		$db = JFactory::getDbo();
		
		$query_code = '
			SELECT MAX(`id`)
			FROM `#__obhelpdesk3_tickets`
		';
		$db->setQuery($query_code);
		$maxID = intval($db->loadResult());
		
		return $maxID.$time;
	}
	
	/**
	 * load Fieldset and output it as a form with all fields loaded
	 * @param unknown $fieldset
	 */
	public static function loadFieldset($form, $fieldset) {
		foreach($form->getFieldset($fieldset) as $field):
			echo '
				<div class="control-group">
					<div class="control-label">
						'.$field->label.'
					</div>
					<div class="controls">
						'.$field->input.'
					</div>
				</div>
			';
		endforeach;
	}
	
	public static function getVersion(){
		$db = JFactory::getDbo();
		$sql = "SELECT * FROM `#__extensions` WHERE `type`='component' AND `element`='com_obhelpdesk'";
		$db->setQuery($sql);
		$res = $db->loadObject();
		$manifest_cache = json_decode($res->manifest_cache);
		$version = $manifest_cache->version;
		return $version;
	}
}