<?php
/**
 * @version   	$Id: content.php 102 2013-12-11 02:03:30Z thongta $
 * @package   	obHelpDesk - kb content plugin
 * @author		http://foobla.com
 * @copyright 	Copyright (C) 2007-2011 foobla.com. All rights reserved.
 * @license   	GNU/GPL, see LICENSE
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
 
class plgobHelpDesk_kbContent extends JPlugin
{
	/**
	 * get config of component obHelpDesk
	 */
	protected function getConfig(){
		$config = JComponentHelper::getParams('com_obhelpdesk');
		return $config;
	}
	
	function onobHelpDeskFaqSearch( &$str_search='',&$res, $faq_manager, $catids=array() ) {
		if( $faq_manager != 'content' ) return;
		if( !$str_search ) return;
		$db = JFactory::getDbo();
		
		# Get config of com_obhelpdesk
		$hd_configs 	= JComponentHelper::getParams( 'com_obhelpdesk' );
		#get config of faq plugin
		//TODO: your code at here
		
		
		$faq_manager 	= $hd_configs->get( 'faq_manager', '' );
		
		if( $faq_manager != 'content' ) return;
		
		$sql_where_cat 	= '';
		if( count($catids) ){
			$sql_where_cat = ' AND b.`id` IN ('.implode(',', $catids).') ';
		}
		
		$sql  = '
				SELECT 
					a.`id` AS `id`,
					a.`title`, 
					a.`title` AS `questions`, 
					a.`alias` AS `alias`, 
					a.`introtext`, 
					a.`introtext` AS `answers`,
					a.`catid` AS `catid`,
					MATCH(a.`title`, a.`introtext`) AGAINST(\''.addslashes($str_search).'\' IN BOOLEAN MODE) AS relevance
				FROM
					`#__content` AS `a`,
					`#__categories` as `b`
				WHERE
					a.`catid` = b.`id` '
					.$sql_where_cat.'
						AND
					MATCH (a.`title`, a.`introtext`) AGAINST(\''.addslashes($str_search).'\' IN BOOLEAN MODE) 
						AND
					a.`state`=1
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
		require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
		for( $i=0; $i<$count; $i++ ){
			$res[$i]->link = JRoute::_(ContentHelperRoute::getArticleRoute($res[$i]->id, $res[$i]->catid));
		}
	}
	
	function onFieldKBCategoryGetOptions( &$options, $faq_manager ) {
		if( $faq_manager != 'content' ) return;
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