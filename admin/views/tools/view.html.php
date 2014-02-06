<?php
/**
* @package		$Id: view.html.php 18 2013-08-06 10:30:40Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
jimport('joomla.html.pane');
/**
 * View to edit a config
 */
class obHelpDeskViewTools extends obView
{
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
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
		$canDo		= obHelpDeskHelper::getActions();
		JToolBarHelper::title(JText::_('OBHELPDESK_TOOLS'), 'tools-new.png');
		JToolBarHelper::preferences('com_obhelpdesk', 500, 800, 'JOPTIONS');
	}
}
