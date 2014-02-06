<?php
/**
* @package		$Id: default.php 99 2013-09-30 03:41:16Z thongta $
* @author 		foobla.com
* @copyright	Copyright (C) 2007-2010 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// ensure a valid entry point
defined('_JEXEC') or die('Restricted Access');

global $option;
if ( isset($overdueTickets) && $overdueTickets ) {
	echo '<table class="table table-striped">';
	$i = 0;
	foreach ($overdueTickets AS $ticket)
	{
		$user			= JFactory::getUser($ticket->user_id); 
		$ticket_link	= JRoute::_('index.php?option='.$option.'&task=ticket.viewdetail&id='.$ticket->id);
		$ticket_title	= $ticket->subject;
		$avatar 		= obHelpDeskUserHelper::getProfileAvatar($ticket->user_id);
		$profile_link	= obHelpDeskUserHelper::getProfileLink($ticket->user_id);
		
		echo '
			<tr>
				<td class="obhelpdesk-entry'.(($i++)%2+1).'">
		';
		/*if($avatar!=NULL) {
			echo '
				<a href="'.JRoute::_($profile_link).'"><img class="hasTip" title="'.$user->name.'" alt="avatar" src="'.$avatar.'" height="32" align="right" /></a>
			';
		}*/
		$str_ticket_time = obHelpDeskHelper::facebookTime($ticket->created);
		echo '
					<span class="label label-department" style="background-color: '.$ticket->label_color.'">
						'.$ticket->prefix.'
					</span>
					<span class="label label-code" style="background-color: '.$ticket->priority_color.'">'.$ticket->id.'</span>
					<a href="'.$ticket_link.'">'.$ticket_title.'</a>
					<small class="pull-right">'.$str_ticket_time.'</small>
				</td>
			</tr>
		';
	}
	echo '</table>';
} else {
	echo '<p>'.JText::_( 'MOD_OBHELPDESK_OVERDUETICKETS_YOU_HAVE_NO_OVERDURE_TICKET' ).'</p>';
}
?>
