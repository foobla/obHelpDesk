<?php
/**
 * @package        $Id: default.php 102 2013-12-11 02:03:30Z thongta $
 * @author         foobla.com
 * @copyright      2007-2014 foobla.com. All rights reserved.
 * @license        GNU/GPL.
 */

// no direct access
defined( '_JEXEC' ) or die;

$document = JFactory::getDocument();
# set page title
$document->setTitle( JText::_( 'COM_OBHELPDESK_NEWTICKET' ) );
?>
<div id="foobla">
	<?php
	require JPATH_COMPONENT . DS . 'helpers' . DS . 'menu.php';
	$menu = new obHelpDeskMenuHelper();
	$menu->topnav( 'newticket' );
	?>
</div>
<h2><?php echo JText::_( 'COM_OBHELPDESK_SELECT_DEPARTMENT' ); ?></h2>

<?php
echo obHelpDeskHelper::loadAnnouncements( 'newticket' );
?>

<div class="list-group">
	<?php foreach ( $this->de_arr as $de ) : ?>
		<?php
		$department_link = JRoute::_( 'index.php?option=com_obhelpdesk&view=ticket&task=newticket&did=' . $de->id );
		if ( $de->external_link != '' ) {
			$department_link = $de->external_link;
		}
		?>
		<a href="<?php echo $department_link; ?>" class="list-group-item">
			<h4 class="list-group-item-heading">
				<?php echo $de->title; ?>
			</h4>

			<p class="list-group-item-text">
				<?php if ( $de->description ) : ?>
					<?php echo $de->description ?>
				<?php endif; ?>
			</p>
		</a>
	<?php endforeach; ?>


</div>