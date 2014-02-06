<?php
/**
 * @package        $Id: default.php 2 2013-07-30 08:16:00Z thongta $
 * @author         foobla.com
 * @copyright      2007-2014 foobla.com. All rights reserved.
 * @license        GNU/GPL.
 */

// no direct access
defined( '_JEXEC' ) or die;

$user = JFactory::getUser();
$is_staff = obHelpDeskUserHelper::is_staff( $user->id );
$document = JFactory::getDocument();
# set page title
$document->setTitle( JText::_( 'COM_OBHELPDESK_SUPPORT_CENTRE' ) );
?>

	<div class="obhelpdesk-dashboard" id="foobla">
		<?php
		require JPATH_COMPONENT . DS . 'helpers' . DS . 'menu.php';
		$menu = new obHelpDeskMenuHelper();
		$menu->topnav( 'dashboard' );
		?>
		<div class="row-fluid">

			<?php
			echo obHelpDeskHelper::loadAnnouncements();
			?>
			<?php
			if ( $is_staff ) {
				// load dashboard for staff
				echo $this->loadTemplate( 'staff' );
			} else {
				// load dashboard for customer
				echo $this->loadTemplate( 'customer' );
			}
			?>
		</div>
	</div>
	<div class="clearfix">&nbsp;</div>
<?php
// if(obhelpdeskHelpers::getConfig('showFooter')->value) {
// 	echo obhelpdeskHelpers::loadFooter();
// }
?>