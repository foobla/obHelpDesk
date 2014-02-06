<?php
/**
* @package		$Id: avatar.php 2 2013-07-30 08:16:00Z thongta $
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
class JFormFieldAvatar extends JFormFieldList
{
	public $type = 'Avatar';
	public $avatars = array(
		'gravatar' => 'Gravatar',
		'com_alphauserpoints' => 'AlphaUserPoints',
		'com_comprofiler' => 'CommunityBuilder',
		'com_community' => 'JoomSocial',
		'com_kunena' => 'Kunena',
		'none' => 'None',
	);
	/**
	 * Method to get a list of avatars
	 */
	protected function getOptions()
	{
		$options = array();
		foreach ($this->avatars as $key => $avatar) {
			$obj = new stdClass();
			$obj->text = $avatar;
			$obj->value = $key;
			$options[] = $obj;
		}
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	
	}
}
