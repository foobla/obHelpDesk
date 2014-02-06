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
 * View to edit a department
 */
class obHelpDeskViewDepartment extends obView
{
	protected $form;
	protected $item;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$model_fields = obModel::getInstance( 'Fields', 'obHelpDeskModel' );
		$this->fields = $model_fields->getItems();
		$this->options = $this->get('Options');
		
		JHTML::stylesheet( 'style.css', 'administrator/components/com_obhelpdesk/assets/' );
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', 1);

		$canDo		= obHelpDeskHelper::getActions();
		@$isNew		= ($this->item->id == 0);
		JToolBarHelper::title(JText::_($isNew ? 'OBHELPDESK_DEPARTMENT_ADD_NEW' : 'OBHELPDESK_DEPARTMENT_EDIT_DEPARTMENT'), 'groups-add');

		if ($canDo->get('core.edit')||$canDo->get('core.create')) {
			JToolBarHelper::apply('department.apply');
			JToolBarHelper::save('department.save');
		}

		if (empty($this->item->id))  {
			JToolBarHelper::cancel('department.cancel');
		} else {
			JToolBarHelper::cancel('department.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
