<?php
/**
* @package		$Id: tools.php 18 2013-08-06 10:30:40Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Tools controller class.
 * @since       1.6
 */
class obHelpDeskControllerTools extends JControllerForm
{
	public function import_obhd(){
		$db = JFactory::getDbo();

		$model = $this->getModel('tools');
		$model->import_obhd();
		
		$msg = JText::_('Imported successful');
		$this->setRedirect('index.php?option=com_obhelpdesk&view=tools', $msg);
	}
}
