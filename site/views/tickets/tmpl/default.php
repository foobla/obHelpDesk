<?php
/**
 * @package        $Id: default.php 103 2013-12-18 10:42:49Z thongta $
 * @author         foobla.com
 * @copyright      2007-2014 foobla.com. All rights reserved.
 * @license        GNU/GPL.
 */

// no direct access
defined( '_JEXEC' ) or die;

// Load the tooltip behavior.
JHtml::_( 'behavior.tooltip' );
JHtml::_( 'behavior.multiselect' );
JHtml::_( 'behavior.modal' );

$document = JFactory::getDocument();
# set page title
$document->setTitle( JText::_( 'COM_OBHELPDESK_TICKETS_LISTING' ) );

$user = JFactory::getUser();
$is_staff = obHelpDeskUserHelper::is_staff( $user->id );
$userId = $user->get( 'id' );
$canDo = obHelpDeskHelper::getActions();
$listOrder = $this->escape( $this->state->get( 'list.ordering' ) );
$listDirn = $this->escape( $this->state->get( 'list.direction' ) );
$canOrder = $user->authorise( 'core.edit.state', 'com_obhelpdesk' );
$saveOrder = $listOrder == 'a.ordering';
?>
<div id="foobla">
	<?php
	if ( JRequest::getString( "format" ) != 'raw' ) {
		require JPATH_COMPONENT . DS . 'helpers' . DS . 'menu.php';
		$menu = new obHelpDeskMenuHelper();
		$menu->topnav( 'tickets' );
	} else {
		$config = JFactory::getConfig();
		?>
		<h3 style="margin: 0; padding: 0; float: left;"><?php echo $config->get( 'sitename' ); ?> > Tickets Listing</h3>
		<!-- Print Button -->
		<a class="obHelpDeskPrint button" rel="nofollow" href="#" onclick="window.print();return false;" style="float: right; margin-bottom: 5px;">
			<span>PRINT THIS PAGE</span>
		</a>
		<div style="clear:both;"></div>
	<?php
	}
	$config = JFactory::getConfig();
	$sef = $config->get( 'sef' );
	$sef_rewrite = $config->get( 'sef_rewrite' );
	$sef_suffix = $config->get( 'sef_suffix' );
	$action = 'index.php';
	if ( $sef ) {
		$action = JRoute::_( 'index.php?option=com_obhelpdesk&view=tickets' );
	}
	?>

	<?php
	echo obHelpDeskHelper::loadAnnouncements('tickets');
	?>

	<form action="<?php echo $action; ?>" method="get" name="adminForm" id="adminForm">
		<?php if ( ! $sef ) { ?>
			<input type="hidden" name="option" value="com_obhelpdesk" />
			<input type="hidden" name="view" value="tickets" />
		<?php } ?>
		<div class="form-inline pull-right" style="position: relative;">
			<?php if ( $this->listStatus ) {
				echo $this->listStatus;
			} ?>
			<?php if ( $this->listDepartment ) {
				echo $this->listDepartment;
			} ?>
			<?php if ( $this->listStaff ) {
				echo $this->listStaff;
			} ?>
			<a class="btn hidden-xs" title="<?php echo JText::_( 'COM_OBHELPDESK_SHOW_HIDE_FILTER_BY_DATE' ) ?>" onclick="ShowHideRange();"><i id="obhelpdesk_filter_range_ico" class="icon-expand"></i></a>
			<?php
			/*$u = JURI::getInstance();
			<a class="btn btn-inverse" rel="nofollow" title="<?php echo JText::_('OBHELPDESK_PRINT')?>" href="<?php echo $u->toString()?>&amp;format=raw" onclick="window.open(this.href,\'printWindow\',\'width=1024,height=800,location=no,menubar=no,resizable=yes,scrollbars=yes\'); return false;"><i class="icon-printer" ></i></a>
			*/
			?>
		</div>
		<div class="form-inline pull-right" id="obhelpdesk_range_filter" style="display:none;">
			<div class="clearfix">&nbsp;</div>
			<div class="input-append">
				<input type="text" class="span2" name="filter_search" id="filter_search" value="<?php echo $this->escape( $this->state->get( 'filter.search' ) ); ?>" title="<?php echo JText::_( 'OBHELPDESK_SEARCH_IN_NAME' ); ?>" />
				<button type="submit" class="btn"><?php echo JText::_( 'OBHELPDESK_FILTER' ); ?></button>
				<button type="button" class="btn" onclick="return resetFilter();"><?php echo JText::_( 'OBHELPDESK_FILTER_CLEAR' ); ?></button>
			</div>
			<?php echo JHTML::_( 'calendar', $this->filter_from, 'filter_from', 'filter_from', '%Y-%m-%d', array( 'size' => '10', 'maxlength' => '10', 'class' => 'span2', 'placeholder' => JText::_( 'FROM_DATE' ) ) ); ?>
			<?php echo JHTML::_( 'calendar', $this->filter_to, 'filter_to', 'filter_to', '%Y-%m-%d', array( 'size' => '10', 'maxlength' => '10', 'class' => 'span2', 'placeholder' => JText::_( 'TO_DATE' ) ) ); ?>
			<button class="btn" onclick="this.form.submit();"><?php echo JText::_( 'SHOW_THIS_RANGE' ); ?></button>
		</div>
		<div class="clearfix visible-xs">&nbsp;</div>

		<?php if ( $this->showBulk ): ?>
			<div id="obhelpdesk_bulk_operator" style="display:none" class="pull-right">
				<div class="clearfix">&nbsp;</div>
				<div class="form-inline">
					<span><strong><?php echo JText::_( 'COM_OBHELPDESK_BULK' ) ?></strong></span>
					<?php if ( $this->listBulkPriority ) {
						echo $this->listBulkPriority;
					} ?>
					<?php if ( $this->listBulkStatus ) {
						echo $this->listBulkStatus;
					} ?>
					<?php if ( $this->listBulkAssignee ) {
						echo $this->listBulkAssignee;
					} ?>
					<?php if ( $this->BulkUpdate ): ?>
						<button class="btn btn-primary" onclick="return obHelpDeskValidate('tickets.bulkupdate');">
							<i class="icon-ok icon-white"></i>&nbsp;<?php echo JText::_( 'COM_OBHELPDESK_UPDATE' ); ?>
						</button>
					<?php endif; ?>
					<button class="btn btn-danger" onclick="return obHelpDeskValidate('tickets.bulkdelete');">
						<i class="icon-trash icon-white"></i>&nbsp;<?php echo JText::_( 'COM_OBHELPDESK_DELETE' ); ?>
					</button>
				</div>
			</div>
		<?php endif; ?>

		<table class="table table-striped">
			<thead>
			<tr class="hidden-xs">
				<th width="2%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_( 'JGLOBAL_CHECK_ALL' ); ?>" onclick="Joomla.checkAll(this); showBulkOperator();" />
				</th>
				<th>
					[<?php echo JHtml::_( 'grid.sort', 'COM_OBHELPDESK_CODE', 'code', $listDirn, $listOrder ); ?> | <?php echo JHtml::_( 'grid.sort', 'COM_OBHELPDESK_PRIORITY', 'priority_ordering', $listDirn, $listOrder ); ?>]
					<?php echo JHtml::_( 'grid.sort', 'COM_OBHELPDESK_SUBJECT', 'a.subject', $listDirn, $listOrder ); ?>
					<?php echo JHtml::_( 'grid.sort', 'COM_OBHELPDESK_CREATED_DATE', 'a.created', $listDirn, $listOrder ); ?>
					<?php echo JHtml::_( 'grid.sort', 'COM_OBHELPDESK_STATUS', 'a.status', $listDirn, $listOrder ); ?>
				</th>
				<th width="15%">
					<?php echo JHtml::_( 'grid.sort', 'COM_OBHELPDESK_LAST_MSG', 'm.reply_time', $listDirn, $listOrder ); ?>
				</th>
				<th width="18%">
					<?php echo JHtml::_( 'grid.sort', 'COM_OBHELPDESK_STAFF', 'staff', $listDirn, $listOrder ); ?>
					<br /><?php echo JHtml::_( 'grid.sort', 'COM_OBHELPDESK_CUSTOMER', 'customer_fullname', $listDirn, $listOrder ); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr class="text-center">
				<td colspan="4" class="hidden-xs">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
				<td colspan="1" class="visible-xs">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php $i = 0;
			if ( is_array( $this->items ) && count( $this->items ) ):
				foreach ( $this->items as $item ) :
					$staff         = JFactory::getUser( $item->staff );
					$ticket_status = '';
					if ( $item->overdue ) {
						$ticket_status .= 'overdue ';
					}

					if ( $item->staff == 0 && $item->status != 'closed' ) {
						$ticket_status .= 'unassigned ';
					} else {
						$ticket_status .= $item->status;
					}
					$item->row_status = trim( $ticket_status );
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="hidden-xs">
							<?php //echo JHtml::_('grid.id', $i, $item->id); ?>
							<input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked); showBulkOperator();" title="<?php echo JText::sprintf( 'JGRID_CHECKBOX_ROW_N', $i + 1 ); ?>">
						</td>

						<td class="<?php echo $item->row_status; ?>">
							<span class="label label-department" style="background-color: <?php echo $item->label_color; ?>"><?php echo $item->prefix; ?></span><span class="label label-code hasTip" style="background-color: <?php echo $item->priority_color; ?>" title="<?php echo JText::_( 'COM_OBHELPDESK_PRIORITY' ) . ': ' . $item->priority_name; ?>"><?php echo $item->id; ?></span>
							<a href="<?php echo JRoute::_( 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . (int) $item->id ); ?>"><?php echo $item->subject; ?></a>
							<a class="pull-right" title="<?php echo JText::_( 'COM_OBHELPDESK_SHOW_HIDE_PREVIEW' ) ?>" onclick="ShowDetailMsg(<?php echo $item->id ?>);" href="javascript:void();"><i id="obhelpdesk_item_<?php echo $item->id ?>_ico" class="icon-expand"></i></a>

							<div id="obhelpdesk_item_<?php echo $item->id ?>" style="display:none;"><?php echo $item->message; ?></div>
							<br />
							<small><?php echo JText::_( 'COM_OBHELPDESK_CREATED_DATE' ) . ' ' . obHelpDeskHelper::facebookTime( $item->created ) ?> (<?php echo $item->replies; ?>&nbsp;<?php echo JText::_( 'OBHELPDESK_TICKET_LIST_REPLIES' ); ?>)</small>
							<small class="visible-xs">by <?php echo obHelpDeskUserHelper::getProfileHolder( $item, true, true ); // #3 customer?>
							</small>
						</td>

						<td class="hidden-xs">
							<small><?php echo obHelpDeskHelper::facebookTime( $item->reply_time ); ?></small>
							<br />
							<small><?php echo JText::_( 'COM_OBHELPDESK_BY' ); ?>&nbsp;<?php
								$last_reply_name = obHelpDeskUserHelper::getNameByEmailAndID( $item->email_reply );
								echo $last_reply_name['firstname'];
								?></small>
						</td>

						<td class="hidden-xs">
							<?php if ( $staff->id ): ?>
								<a class="hasTip" title="<?php echo $staff->name; ?>"><?php $staff_name = explode( ' ', $staff->name );
									echo $staff_name[0]; ?></a>
							<?php else: ?>
								<?php echo JText::_( 'COM_OBHELPDESK_UNASSIGNED' ); ?>
							<?php endif; ?>
							<?php if ( $is_staff ) : ?>
								<br />
								<?php echo obHelpDeskUserHelper::getProfileHolder( $item, true, true ); // #3 customer?>
							<?php endif; ?>
						</td>
					</tr>
					<?php
					$i ++;
				endforeach;
			endif;
			?>
			</tbody>
		</table>
		<input type="hidden" name="task" value="list" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="operator" value="0" />
		<input type="hidden" name="option" value="com_obhelpdesk" />
		<input type="hidden" name="view" value="tickets" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php #echo JHtml::_('form.token'); ?>
	</form>
</div>
<script type="text/javascript">
	function ShowDetailMsg(itemid) {
		id_ico = 'obhelpdesk_item_' + itemid + '_ico';
		id_div = 'obhelpdesk_item_' + itemid;
		if (document.getElementById(id_ico).className == 'icon-expand') {
			document.getElementById(id_ico).className = 'icon-contract';
			document.getElementById(id_div).style.display = '';
		} else {
			document.getElementById(id_ico).className = 'icon-expand';
			document.getElementById(id_div).style.display = 'none';
		}
	}

	function showBulkOperator() {
		if (document.adminForm.boxchecked.value == 0) {
			document.getElementById('obhelpdesk_bulk_operator').style.display = 'none';
		} else {
			document.getElementById('obhelpdesk_bulk_operator').style.display = '';
		}
	}

	function ShowHideRange() {
		if (document.getElementById('obhelpdesk_range_filter').style.display == '') {
			document.getElementById('obhelpdesk_range_filter').style.display = 'none';
			document.getElementById('obhelpdesk_filter_range_ico').setAttribute('class', 'icon-expand');
		} else {
			document.getElementById('obhelpdesk_range_filter').style.display = ''
			document.getElementById('obhelpdesk_filter_range_ico').setAttribute('class', 'icon-contract');
		}
	}

	function obHelpDeskValidate(button) {
		if (button == 'tickets.bulkdelete') {
			if (document.adminForm.boxchecked.value == 0) {
				alert('<?php echo JText::_('COM_OBHELPDESK_MSG_MAKE_SELECT_FIRST');?>');
				return false;
			} else {
				del = confirm('<?php echo JText::_('COM_OBHELPDESK_MSG_SURE');?>');
				if (del == true)
					Joomla.submitbutton('tickets.bulkdelete');
				return false;
			}
		}

		if (button == 'tickets.bulkupdate') {
			if (document.adminForm.boxchecked.value == 0 || document.adminForm.operator.value == 0) {
				alert('<?php echo JText::_('COM_OBHELPDESK_MSG_MAKE_SELECT_FIRST');?>');
				return false;
			} else {
				update = confirm('<?php echo JText::_('COM_OBHELPDESK_MSG_SURE');?>');
				if (update == true)
					Joomla.submitbutton('tickets.bulkupdate');
				return false;
			}
		}
	}

	function resetFilter() {
		document.getElementById('filter_search').value = '';
		document.getElementById('filter_from').value = '';
		document.getElementById('filter_to').value = '';
		if (document.getElementById('filter_department')) document.getElementById('filter_department').value = 0;
		if (document.getElementById('filter_staff')) document.getElementById('filter_staff').value = 'all';
		if (document.getElementById('filter_status')) document.getElementById('filter_status').value = 'open';
		document.adminForm.submit();
	}
</script>