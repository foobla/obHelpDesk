<?php
/**
 * @version   	$Id: k2category.php 2 2013-07-30 08:16:00Z thongta $
 * @package   	obHelpDesk - kb content plugin
 * @author		http://foobla.com
 * @copyright 	Copyright (C) 2007-2011 foobla.com. All rights reserved.
 * @license   	GNU/GPL, see LICENSE
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

JFormHelper::loadFieldClass('list');
class JFormFieldK2Category extends JFormFieldList
{
	public $_name = 'K2Category';
	
	protected function getOptions()
	{
		$app 	= JFactory::getApplication();
		$db 	= JFactory::getDbo();
		$query 	= "
				SELECT 
					`id`, 
					`name`, 
					`name` AS `title`,
					`parent`, 
					`parent` AS `parent_id`
				FROM 
					`#__k2_categories`
				WHERE 
					`published`=1";
		$db->setQuery($query);
		$mitems = $db->loadObjectList();

		if($db->getErrorNum()){
			$app->enqueueMessage(print_r($db->getErrorMsg(), true),'error');
		}
		$children = array();
		if ($mitems)
		{
			foreach ($mitems as $v)
			{
				$pt = $v->parent;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}
		$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
		$mitems = array();
		@$mitems[] = JHTML::_('select.option', 0, JText::_('PLG_OBHELPDESK_KB_CONTENT_ALL_CATEGORIES'));
		foreach ($list as $item)
		{
			$item->treename = JString::str_ireplace('&#160;', '- ', $item->treename);
			@$mitems[] = JHTML::_('select.option', $item->id, $item->treename);
		}
		return $mitems;
	}
}
