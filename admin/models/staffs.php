<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of staff records.
 */
class obHelpDeskModelStaffs extends JModelList
{
	/**
	 * Constructor.
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'group_id', 'a.group_id',
				'user_id', 'a.user_id',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Adjust the context to support modal layouts.
		if ($layout = JRequest::getVar('layout', 'default'))
		{
			$this->context .= '.'.$layout;
		}

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// List state information.
		parent::populateState('u.name', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$jv 	= new JVersion();
		$isJ25 	= ($jv->RELEASE == '2.5' );
		if( $isJ25 ) {
			$query->select(
					$this->getState(
							'list.select',
							'a.*, u.username as username, u.email as email, u.name as uname, u.usertype,  g.title as gname'
					)
			);
		} else {
			$query->select(
					$this->getState(
							'list.select',
							'a.*, u.username as username, u.email as email, u.name as uname, g.title as gname'
					)
			);
		}
		

		$query->from($db->quoteName('#__obhelpdesk3_staffs').' AS a');
		$query->join('LEFT', '#__users AS u ON u.id = a.user_id');
		$query->join('LEFT', '#__obhelpdesk3_groups AS g ON g.id = a.group_id');
		// If the model is set to check item state, add to the query.
		$state = $this->getState('filter.published');

		if (is_numeric($state))
		{
			$query->where('a.published = '.(int) $state);
		}

		// Filter the items over the search string if set.
		if ($this->getState('filter.search'))
		{
			var_dump($this->getState('filter.search'));
			// Escape the search token.
			$token	= $db->Quote('%'.$db->escape($this->getState('filter.search')).'%');

			// Compile the different search clauses.
			$searches	= array();
			$searches[]	= 'u.username LIKE '.$token;
			$searches[]	= 'u.name LIKE '.$token;

			// Add the clauses to the query.
			$query->where('('.implode(' OR ', $searches).')');
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.title')).' '.$db->escape($this->getState('list.direction', 'ASC')));
		return $query;
	}

}
