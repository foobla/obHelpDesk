<?php
/**
* @package		$Id: kbcategory.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 */
class JFormFieldKBCategory extends JFormFieldList
{
	public $type = 'KBCategory';
	/**
	 * Method to get a list of faqs manager
	 */
	protected function getOptions()
	{
		$app 			= JFactory::getApplication();
		JPluginHelper::importPlugin('obhelpdesk_kb');
		$configs 		= JComponentHelper::getParams('com_obhelpdesk');
		$faq_manager 	= $configs->get('faq_manager');
		$dispatcher 	= JDispatcher::getInstance();
		$options 		= array();
 		$dispatcher->trigger( 'onFieldKBCategoryGetOptions', array(&$options, $faq_manager ) );
 		return $options;
	}
}