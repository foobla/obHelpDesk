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
 * View to edit a replytemplate
 */
class obHelpDeskViewReplytemplate extends obView
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
		$this->templatekey 		= $this->get('TemplateKey');
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
		JToolBarHelper::title(JText::_($isNew ? 'OBHELPDESK_REPLY_TEMPLATE_ADD_NEW_REPLY_TEMPLATE' : 'OBHELPDESK_REPLY_TEMPLATE_EDIT_REPLY_TEMPLATE'), 'reply-template.png');

		if ($canDo->get('core.edit')||$canDo->get('core.create')) {
			JToolBarHelper::apply('replytemplate.apply');
			JToolBarHelper::save('replytemplate.save');
		}

		if (empty($this->item->id))  {
			JToolBarHelper::cancel('replytemplate.cancel');
		} else {
			JToolBarHelper::cancel('replytemplate.cancel', 'JTOOLBAR_CLOSE');
		}
	}

}
