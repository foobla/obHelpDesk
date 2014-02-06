<?php
/**
* @package		$Id: edit_groups.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

$current_groups = array(1);
if($this->item->usergroups) {
	$current_groups = explode(',', $this->item->usergroups);
}

?>
<?php echo JHtml::_('access.usergroups', 'jform[usergroups]', $current_groups, true); ?>