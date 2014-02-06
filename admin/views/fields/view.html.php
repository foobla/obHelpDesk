<?php
/**
* @package		$Id: view.html.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of users.
 */
class obHelpDeskViewFields extends obView
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		$canDo	= obHelpDeskHelper::getActions();

		JToolBarHelper::title(JText::_('OBHELPDESK_FIELDS_CUSTOM_FIELDS_MANAGER'), 'user');

		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('field.add');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('field.edit');
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::divider();
			JToolBarHelper::publish('fields.publish', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::unpublish('fields.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'fields.delete');
			JToolBarHelper::divider();
		}
		
		if (JFactory::getUser()->authorise('core.admin')) {
			JToolBarHelper::checkin('fields.checkin');
		}
		
		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
		}
	}
}
