<?php
/**
 * @version		$Id: department.php 2 2013-07-30 08:16:00Z thongta $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 */
class JFormFieldDepartment extends JFormFieldList
{
	public $type = 'Department';

	/**
	 * Method to get a list of departments
	 */
	protected function getOptions()
	{
		// Initialise variables.
		$options = array();
		$name = (string) $this->element['name'];

		// Let's get the id for the current item, either category or content item.
		$jinput = JFactory::getApplication()->input;
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('a.id AS value, a.title AS text, a.published');
		$query->from('#__obhelpdesk3_departments AS a');
		$query->where('a.published = 1');

		$query->order('a.title ASC');
		$db->setQuery($query);
		$options = $db->loadObjectList();
		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	
	}
}
