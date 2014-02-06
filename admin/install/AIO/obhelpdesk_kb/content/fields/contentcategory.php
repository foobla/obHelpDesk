<?php
/**
 * @version   	$Id: contentcategory.php 2 2013-07-30 08:16:00Z thongta $
 * @package   	obHelpDesk - kb content plugin
 * @author		http://foobla.com
 * @copyright 	Copyright (C) 2007-2011 foobla.com. All rights reserved.
 * @license   	GNU/GPL, see LICENSE
 */

defined('JPATH_BASE') or die();

JFormHelper::loadFieldClass('list');
class JFormFieldContentCategory extends JFormFieldList
{
	public $_name = 'ContentCategory';
	
	protected function getOptions()
	{
		$app 	= JFactory::getApplication();
		$db 	= JFactory::getDbo();
		$query = "
				SELECT 
					`id`, 
					`parent_id`,
					`parent_id` AS `parent`, 
					`title`,
					`title` AS `name`
				FROM
					`#__categories`
				WHERE
					`extension` = 'com_content'
					AND `published`=1;";
		$db->setQuery($query);
				
/* 				'SELECT 
						`hc`.`category_id` AS `id`,
						`hc`.`category_parent_id` as `parent`,
						`hc`.`category_parent_id` as `parent_id`,
						`hc`.`category_name` as `title`,
						`hc`.`category_name` as `name`,
						`hc`.*
					FROM
						`#__hikashop_category` AS `hc`
					WHERE
						`hc`.`category_type` = "product" AND `hc`.`category_published` = 1
					ORDER BY `hc`.`category_ordering`';
		$db->setQuery($query); */
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
		$list = JHTML::_('menu.treerecurse', 1, '', array(), $children, 9999, 0, 0);
		$mitems = array();
		@$mitems[] = JHTML::_('select.option', 0, JText::_('PLG_OBHELPDESK_KB_CONTENT_ALL_CATEGORIES'));
		foreach ($list as $item)
		{
			$item->treename = JString::str_ireplace('&#160;', '- ', $item->treename);
			@$mitems[] = JHTML::_('select.option', $item->id, $item->treename);
		}
		return $mitems;
// 		return JHTML::_('select.genericlist', $mitems, $this->name, $onChange.' class="inputbox" multiple="multiple" size="15"', 'value', 'text', $this->value, $this->id);
	}
}
