<?php
/**
* @package		$Id: userprofiler.php 2 2013-07-30 08:16:00Z thongta $
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
class JFormFieldUserprofiler extends JFormFieldList
{
	public $type = 'Userprofiler';
	public $init = array(
		'com_alphauserpoints' => 'AlphaUserPoints',
		'com_comprofiler' => 'CommunityBuilder',
		'com_community' => 'JomSocial',
		'com_kunena' => 'Kunena',
		'custom' => 'Custom',
		'none' => 'None',
	);
	/**
	 * Method to get a list of languages
	 */
	protected function getOptions()
	{
		# get current value
		$db = JFactory::getDBO();
		$options = array();
		foreach ($this->init as $key => $v) {
			$obj = new stdClass();
			$obj->text = $v;
			$obj->value = $key;
			$options[] = $obj;
		}
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	
	}
}
