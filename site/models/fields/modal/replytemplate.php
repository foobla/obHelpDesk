<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Field to select a user id from a modal list.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       1.6.0
 */
class JFormFieldModal_ReplyTemplate extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6.0
	 */
	public $type = 'Modal_ReplyTemplate';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6.0
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$link = 'index.php?option=com_obhelpdesk&amp;view=replytemplates&amp;tid='.$this->value.'&amp;layout=modal&amp;tmpl=component&amp;field='.$this->id;

		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal_' . $this->id);

		// Build the script.
		$script = array();
		$script[] = '	function jSelectTpl_' . $this->id . '(id, content) {';
		$script[] = '		document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '		body_id = "ticket_message";';
		$script[] = '		rte_id = "rte_" + body_id;';
		$script[] = '		ifm = document.id(rte_id);';
		$script[] = '		myeditor = ifm.contentWindow.document;';
		$script[] = '		myeditor.body.innerHTML = atob(content);';
		$script[] = '		console.log(atob(content));';
		$script[] = '		doCheck();';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Create a dummy text field with the user name.
		$html[] = '<div class="form-inline">';
		// Create the user select button.
		if ($this->element['readonly'] != 'true')
		{
			$html[] = '		<a class="btn btn-small btn modal_' . $this->id . '" title="' . JText::_('COM_OBHELPDESK_LOAD_REPLY_TEMPLATE') . '"' . ' href="' . $link . '"'
				. ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
			$html[] = '			' . JText::_('COM_OBHELPDESK_LOAD_REPLY_TEMPLATE') . '</a>';
		}
		$html[] = '</div>';

		// Create the real field, hidden, that stored the user id.
		$html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . (int) $this->value . '" />';

		return implode("\n", $html);
	}
}
