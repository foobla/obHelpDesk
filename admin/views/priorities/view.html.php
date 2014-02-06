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

class obHelpDeskViewPriorities extends obView
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

		JHTML::stylesheet( 'style.css', 'administrator/components/com_obhelpdesk/assets/' );
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		$canDo	= obHelpDeskHelper::getActions();

		JToolBarHelper::title(JText::_('OBHELPDESK_PRIORITY_MANAGER'), 'priority.png');

		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('priority.add');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('priority.edit');
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::divider();
			JToolBarHelper::publish('priorities.publish', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::unpublish('priorities.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
		}
		
		if (JFactory::getUser()->authorise('core.admin')) {
			JToolBarHelper::checkin('priorities.checkin');
		}
		
		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'priorities.delete');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
		}
	}
} // end class
?>