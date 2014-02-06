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
 * View to edit a staff
 */
class obHelpDeskViewField extends obView
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
		$isNew		= ($this->item->id == 0);
		JToolBarHelper::title(JText::_($isNew ? 'OBHELPDESK_FIELD_ADD_NEW' : 'OBHELPDESK_STAFF_EDIT_STAFF'), 'staffs-new.png');

		if ($canDo->get('core.edit')||$canDo->get('core.create')) {
			JToolBarHelper::apply('field.apply');
			JToolBarHelper::save('field.save');
		}

		if (empty($this->item->id))  {
			JToolBarHelper::cancel('field.cancel');
		} else {
			JToolBarHelper::cancel('field.cancel', 'JTOOLBAR_CLOSE');
		}
	}

}
