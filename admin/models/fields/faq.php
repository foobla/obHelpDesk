<?php
/**
* @package		$Id: faq.php 2 2013-07-30 08:16:00Z thongta $
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
class JFormFieldFAQ extends JFormField
{
	public $type = 'FAQ';
	public $init = array(
		'com_quickfaq' => 'QuickFAQ',
		'com_jefaq' => 'JE FAQ',
		'com_jefaqpro' => 'JE FAQ PRO',
		'com_content' => 'Content',
		'none' => 'None',
	);
	
	protected function getInput(){
		$app 			= JFactory::getApplication();
		JPluginHelper::importPlugin('obhelpdesk_kb');
		$dispatcher 	= JDispatcher::getInstance();
		$options 		= $this->getOptions();
		
		$faq_configs 	= array();
// 		$dispatcher->trigger( 'onobHelpDeskFaqFieldGetInput', array(&$options, &$faq_configs, $this->formControl, $this->group,$this->fieldname ) );
		$select_box = JHTML::_('select.genericlist',  $options, $this->name, '', 'element', 'name', $this->value, $this->id);

		$divs 		= (count($faq_configs))?implode('',$faq_configs):'';
		$plugin_manager_link = JURI::base().'index.php?option=com_plugins&filter_folder=obhelpdesk_kb';

		$html 		= "<div style=\"position: relative; float: left;\">{$select_box}".
						"<div style=\"clear:both;\">Click to <a href=\"{$plugin_manager_link}\" target=\"blank\">config obHelpDeskFaq Plugins</a></div>";
		
		return $html;
	}


	/**
	 * Method to get a list of faqs manager
	 */
	protected function getOptions()
	{
		$db = JFactory::getDbo();
		$sql = "
				SELECT 
				    `element`,
					`name`
				FROM
				    `#__extensions` 
				WHERE
				    `type` = 'plugin' 
				        AND `folder` = 'obhelpdesk_kb' 
				        AND `enabled` = 1";
		$db->setQuery( $sql );
		$rows = $db->loadObjectList();
		return $rows;
	}
}