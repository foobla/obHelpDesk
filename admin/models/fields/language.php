<?php
/**
* @package		$Id: language.php 2 2013-07-30 08:16:00Z thongta $
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
class JFormFieldLanguage extends JFormFieldList
{
	public $type = 'Language';

	/**
	 * Method to get a list of languages
	 */
	protected function getOptions()
	{
		// Initialise variables.
		$options = array();

		$name = (string) $this->element['name'];
		
		// Let's get the id for the current item, either category or content item.
		$jinput = JFactory::getApplication()->input;
		
		$languages_file = JPATH_COMPONENT_SITE.DS.'helpers'.DS.'gtranslate'.DS.'languages.ini';
		$lines = explode("\n", JFile::read($languages_file));
		
		$options 	= array();
		
		for ($i=0; $i<count($lines); $i++) {
			$x = explode("=", $lines[$i]);
			$obj = new stdClass();
			$obj->text = ucwords(strtolower(trim($x[0])));
			$obj->value = trim($x[1]);
			
			$options[] = $obj;
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	
	}
}
