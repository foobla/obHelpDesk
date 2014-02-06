<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML Article View class for the Content component
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since		1.5
 */
class obHelpDeskViewReplyTemplates extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;

	public function display($tpl = null)
	{
		// Get model data.
		$this->state		= $this->get('State');
		$this->item			= $this->get('Item');
		$this->form			= $this->get('Form');
		
		// require BBCode Editor.
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'editor_bbcode.php';
		$editor_bbcode = new ObEditorBBcode();
		$value = '';
		if(isset($this->item->content)) $value = obHelpDeskHelper::html2bbcode($this->item->content);
		$editor_ticket_message = $editor_bbcode->display( 'ticket_message', $value, array('bold','italic','underline','hypelink','image','list','color','quote','source') );
		$this->editor_message = $editor_ticket_message;
		parent::display($tpl);
	}
}
