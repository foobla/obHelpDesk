<?php
/**
 * @version   	$Id: k2.php 48 2013-09-03 04:08:14Z phonglq $
 * @package   	obHelpDesk - kb content plugin
 * @author		http://foobla.com
 * @copyright 	Copyright (C) 2007-2011 foobla.com. All rights reserved.
 * @license   	GNU/GPL, see LICENSE
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgobHelpDesk_kbK2 extends JPlugin
{ 
	function onobHelpDeskFaqSearch( &$str_search='',&$res , $faq_manager,$catids=array() ) {
		if( $faq_manager != 'k2' ) return;
		if( !$str_search ) return;
		$db = JFactory::getDbo();
		#TODO: get plugin parram
		
		$sql_where_cat 	= '';
		if( count($catids) ){
			$sql_where_cat = ' AND b.`id` IN ('.implode(',', $catids).') ';
		}
		
		$sql  = '
				SELECT 
					a.`id`,
					a.`title` , 
					a.`title` AS `questions`, 
					a.`alias`, 
					a.`introtext`, 
					a.`introtext` AS `answers`,
					a.`catid` AS `catid`,
					MATCH(a.`title`, a.`introtext`) AGAINST(\''.addslashes($str_search).'\' IN BOOLEAN MODE) AS relevance
				FROM
					`#__k2_items` AS `a`,
					`#__k2_categories` as `b`
				WHERE
					a.`catid` = b.`id` '
					.$sql_where_cat.'
						AND
					MATCH (a.`title`, a.`introtext`) AGAINST(\''.addslashes($str_search).'\' IN BOOLEAN MODE) 
						AND
					a.`published`=1
					ORDER BY relevance ASC';

		$db->setQuery($sql);
		$res 	= $db->loadObjectList();
		$count = count($res);
		if($db->getErrorNum()){
// 			echo '<pre>' . print_r( $db->getErrorMsg(), true ) . '</pre>';
		}
		if(!$res){
// 			echo '<h1>NULL</h1>';
		}
		
		$filename = JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php';
		if( JFile::exists($filename) ) {
			require_once $filename;
		}

		for( $i=0; $i<$count; $i++ ){
			$res[$i]->link 	= K2HelperRoute::getItemRoute($res[$i]->id.':'.urlencode($res[$i]->alias), $res[$i]->catid);
		}
	}
	
	function onFieldKBCategoryGetOptions( &$options, $faq_manager ) {
		if($faq_manager!='k2') return;
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
		$options = array();
		$options[] = JHTML::_('select.option', 0, JText::_('PLG_OBHELPDESK_KB_CONTENT_ALL_CATEGORIES'));
		foreach ($list as $item)
		{
			$item->treename = JString::str_ireplace('&#160;', '- ', $item->treename);
			$options[] = JHTML::_('select.option', $item->id, $item->treename);
		}
		return $options;
	}
}