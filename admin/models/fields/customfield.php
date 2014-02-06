<?php
/**
* @package		$Id: customfield.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

class JFormFieldCustomfield extends JFormFieldList
{
	public $type = 'Customfield';
	
	public $array_type = array(
		'text' => 'Text',
		'list' => 'Select',
		'radio' => 'Radio',
		'checkbox' => 'Checkbox',
		'checkboxes' => 'Checkbox list',
		'calendar' => 'Calendar',
		'textarea' => 'Textarea',
		'datetime' => 'Datetime',
	);
	
	/**
	 * Method to get a list of custom fields
	 */
	protected function getOptions()
	{
		// Initialise variables.
		$arr_options = $this->array_type;
		foreach ($arr_options as $key=>$option) {
			$arr_options[$key] = JText::_($option);
		}
		return $this->array_type;
	}
}
