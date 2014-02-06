<?php
/**
 * @version		$Id: staffs.php 2 2013-07-30 08:16:00Z thongta $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 */
class JFormFieldStaffs extends JFormFieldList
{
	public $type = 'Staffs';

	/**
	 * Method to get a list of staffs
	 */
	protected function getOptions()
	{
		// Initialise variables.
		$options = array();

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('a.user_id AS value, u.name AS text');
		$query->from('#__obhelpdesk3_staffs AS a');
		$query->join('LEFT', '#__users AS u ON u.id = a.user_id');

		$query->order('u.name ASC');

		// Get the options.
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
