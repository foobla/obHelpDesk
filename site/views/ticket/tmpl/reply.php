<?php
/**
* @package		$Id: reply.php 103 2013-12-18 10:42:49Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

$jv = new JVersion();
$isJ25 = ( $jv->RELEASE == '2.5' );
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
if( $isJ25 ){
	JHTML::_('behavior.mootools');
} else {
	
}
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.modal');

$params = $this->form->getFieldsets('params');

// set page title
$document = JFactory::getDocument();
$page_title = html_entity_decode( $this->item->subject, ENT_COMPAT, "UTF-8" );

$document->setTitle('['.$this->item->code.'] '.$page_title);
?>
<script type="text/javascript">

	/**
	 * Apply Reply Template
	 * 
	 */
	function getReplyTemplate( template_id ) {
		var customer_id 	= <?php echo $this->item->customer_id;?>;
		var customer_email 	= '<?php echo $this->item->customer_email;?>';
		var url 			= 'index.php?option=com_obhelpdesk&task=replytemplates.getreplytemplate&template_id='+template_id+'&customer_id='+customer_id+'&customer_email='+customer_email+'&tmpl=component';
		var myAjax = new Request({
			url:url,
			method: 'get',
			evalScripts: true,
			onSuccess: function(responseText, responseXML) {
				if ( responseText!= '' ) {
					obhdEditorChangeContent('ticket_message', responseText);
				}
			}
		}).send();
	}
	
	/**
	 * Toggle Messsage
	 * 
	 */
	function obHDToogleMessage( msg_id ){
		$('obhd_messsage_'+msg_id).toggleClass('ob_show');
	}


	function obhd_load_more_message(){
		var lastdiv =  $('obhd_messages_wrap').getLast('div');
		var last_msg_id = parseInt((lastdiv.id).substr(14));
		var ticket_id = <?php echo $this->item->id;?>;
		var url = 'index.php?option=com_obhelpdesk&task=ticket.loadmoremsg&tid='+ticket_id+'&last_msg_id='+last_msg_id;
		var myAjax = new Request({
			url:url,
			method: 'get',
			evalScripts: true,
			onSuccess: function(responseText, responseXML) {
				if ( responseText!= '' ) {
					$('obhd_messages_wrap').innerHTML += (responseText);
				}
			}
		}).send();
	}

	function obhdEditorChangeContent(body_id, content) {
			rte_id 	= "rte_" + body_id;
			ifm = document.id(rte_id);
			ifm.contentWindow.focus();
			myeditor 	= ifm.contentWindow.document;
			myeditor.body.innerHTML = atob(content);
			ifm.contentWindow.find('{cursor}');
			myeditor.execCommand( 'delete', false, null );
			doCheck();
			SqueezeBox.close();
	}
	
	function opentListReplyTemplate( ) {
		SqueezeBox.open( null, {handler: 'iframe', size: {x: 800, y: 500},'url':'index.php?option=com_obhelpdesk&view=replytemplates&tmpl=component&layout=modal'});
	}

	window.addEvent('domready', function() {
		ifm = document.id('rte_ticket_message');
		ifm.contentWindow.focus();
		myeditor 	= ifm.contentWindow.document;
		ifm.contentWindow.find('{cursor}');
		myeditor.execCommand( 'delete', false, null );
	});

	function loadfields(form ) {
		var did = document.getElementById('jformdepartmentid').value;
		var tid = form.id.value;
		var url = 'index.php?option=com_obhelpdesk&task=ticket.loadcustomfield&tid='+tid+'&did='+did;
		var myAjax = new Request({
			url:url,
			method: 'get',
			evalScripts: true,
			evalResponse: true,
			onSuccess: function(respon) {
				t = eval(respon);
				console.log(t.length);
				x = document.getElementById('tr_departmentlist');
				$$('.customfields').each(function(e){
					e.dispose();
				});
				for( i=t.length-1; i>=0; i-- ) {
					r=t[i];
					td1 = new Element('td',{'style':"text-align: right;"});
					td1.innerHTML = r.td1;
					td2 = new Element('td');
					td2.innerHTML = r.td2;
					tr = new Element('tr',{'class':'customfields'});
					tr.appendChild(td1);
					tr.appendChild(td2);
					tr.inject(x, 'after');
				}
			}
		}).send();
	}
</script>

<div id="foobla">
<?php 
	require JPATH_COMPONENT.DS.'helpers'.DS.'menu.php';
	$menu = new obHelpDeskMenuHelper();
	$menu->topnav('tickets');
?>
	<h3>
		<span class="label label-ticket" style="background-color: <?php echo $this->department->label_color; ?>">
			<?php echo $this->item->code;?>
		</span>
		&nbsp;<?php echo $this->item->subject; ?>
	</h3>
	
	<div class="obhelpdesk-profile-bar">
		<?php
		if ($this->is_staff) {
			JPluginHelper::importPlugin('obhelpdesk');
			$dispatcher = JDispatcher::getInstance();
			$results 	= $dispatcher->trigger('onLoadProfile', array($this->item));
			if (count($results))
			foreach($results AS $result) {
				echo $result;
			}
		}
		?>
		<div class="btn-group pull-right">
			<button class="btn btn-mini" id="show-ticket-detail-btn" title="<?php echo JText::_('OBHELPDESK_SHOW_TICKET_DETAILS')?>" onclick="ShowDetailTicket();"><i id="icon-detail-ticket" class="icon-expand icon-white" ></i>&nbsp;<?php echo JText::_('COM_OBHELPDESK_TICKET_DETAILS')?></button>
			<button class="btn btn-mini dropdown-toggle" data-toggle="dropdown">
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
				<?php if($this->item->status != 'closed'):?>
				<?php if($this->perm_update):?>
				<li>
					<a class="" title="<?php echo JText::_('COM_OBHELPDESK_CLOSE_THIS_TICKET')?>" onclick="return obHelpDeskValidate('ticket.close')"><i class="icon-lock icon-white" ></i>&nbsp;<?php echo JText::_('COM_OBHELPDESK_CLOSE_THIS_TICKET')?></a>
				</li>
				<?php endif;?>
				
				<?php else:?>
				<li>
					<a class="" title="<?php echo JText::_('COM_OBHELPDESK_REOPEN_THIS_TICKET')?>" onclick="return obHelpDeskValidate('ticket.reopen')"><i class="icon-undo icon-white" ></i>&nbsp;<?php echo JText::_('COM_OBHELPDESK_REOPEN_THIS_TICKET')?></a>
				</li>
				<?php endif;?>
				
				<?php if($this->perm_delete):?>
				<li>
					<a class="" title="<?php echo JText::_('COM_OBHELPDESK_DELETE_THIS_TICKET')?>" onclick="return obHelpDeskValidate('ticket.remove')"><i class="icon-remove icon-white" ></i>&nbsp;<?php echo JText::_('COM_OBHELPDESK_DELETE_THIS_TICKET')?></a>
				</li>
				<?php endif;?>
			</ul>
		</div>
		<div class="clearfix"></div>
	</div>
	
	<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_obhelpdesk'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
		<table class="table table-bordered" id="obhelpdesk-ticket-detail" style="display:none;">
			<tr id="tr_departmentlist">
				<td><strong><?php echo JText::_('OBHELPDESK_DEPARTMENT');?></strong></td>
				<td><?php echo $this->departmentlist;?></td>
			</tr>
		<?php 
		if(count($this->fields)) :
			foreach ($this->fields as $field) :
		?>
			<tr class="customfields">
				<td style="text-align: right;">
				<label id="jform_obhelpdesk_<?php echo $field->id?>-lbl" for="jform_obhelpdesk_<?php echo $field->id?>" class="hasTip" title="<?php echo $field->title;?>::<?php echo $field->helptext;?>" ><?php echo $field->title; if($field->required) echo '<code>*</code>'?></label>
				</td>
				<td>
					<?php echo obHelpDeskFieldsHelper::printField($field); ?>
				</td>
			</tr>
		<?php 
			endforeach;
		endif;
		?>
		
			<tr>
				<td colspan="2"><strong><?php echo JText::_('COM_OBHELPDESK_TICKET_DETAILS');?></strong></td>
			</tr>
			<tr>
				<td style="text-align:right"><label id="jform_obhelpdesk_subject" for="jform_obhelpdesk_subject"><?php echo JText::_('COM_OBHELPDESK_SUBJECT');?><code>*</code></label></td>
				<td><?php echo $this->form->getInput('subject', null, $this->item->subject);?></td>
			</tr>
			<tr>
				<td style="text-align:right"><label id="jform_obhelpdesk_staff" for="jform_obhelpdesk_staff"><?php echo JText::_('COM_OBHELPDESK_STAFF');?></label></td>
				<td><?php if($this->listAssignee) echo $this->listAssignee; else echo JText::_('OBHELPDESK_UNASSIGNED');?>
			</tr>
			<tr>
				<td style="text-align:right"><label id="jform_obhelpdesk_staff" for="jform_obhelpdesk_staff"><?php echo JText::_('COM_OBHELPDESK_PRIORITY');?></label></td>
				<td><?php if($this->listPriority) echo $this->listPriority;?>
			</tr>
			<tr>
				<td style="text-align:right"><label id="jform_obhelpdesk_staff" for="jform_obhelpdesk_staff"><?php echo JText::_('COM_OBHELPDESK_STATUS');?></label></td>
				<td><?php if($this->listStatus) echo $this->listStatus;?>
			</tr>
			<?php if($this->item->status != 'closed') : ?>
			<?php if($this->updatePerm):?>
			<tr>
				<td colspan="2"><button type="button" class="btn btn-small btn-primary" onClick="return Joomla.submitbutton('ticket.update')"><i class="icon-ok icon-white"></i> <?php echo JText::_('COM_OBHELPDESK_UPDATE')?></button></td>
			</tr>
			<?php endif;?>
			<?php endif;?>
		</table>
		<table class="table table-bordered">
			<?php if($this->item->status != 'closed'):?>
				<?php if($this->perm_answer):?>
				<tr>
					<td colspan="2">
						<div id="obhelpdesk-ticket-message"><?php echo $this->content; ?></div>
						<?php // if($this->is_staff) echo $this->form->getInput('replytemplate' , null, $this->item->id);?>
						
						<?php 
						if($this->department->file_upload != 'no'):
						?>
						<p>
						<strong><span id="add_anothor_file" onclick="add_anothor_file();"><i class="icon-flag-2"></i><?php echo JText::_('OBHELPDESK_ADD_ATTACHMENT'); ?></span></strong>
						<br />
						<div id="files_upload">
						<span class="label" id="allowed_file_ext" style="display:none;"><?php echo JText::_('OBHELPDESK_ALLOWED_FILE_EXT'); ?> <?php echo $this->department->file_upload_extensions;?></span>
						</div>
						<input type="hidden" name="count_click_add" id="count_click_add" value="0">
						</p>
						<?php 
						endif;
						?>
						
						<div class="btn-group">
						    <button class="btn btn-primary .btn-large" onclick="return Joomla.submitbutton('ticket.reply')"><i class="icon-chevron-right icon-white"></i> <?php echo JText::_('OBHELPDESK_SEND_REPLY'); ?></button>
						    <button class="btn btn-primary .btn-large dropdown-toggle" data-toggle="dropdown">
						    	<span class="caret"></span>
						    </button>
						    <ul class="dropdown-menu">
						    	<?php if($this->is_staff) :?>
						    	<li><a href="#" onclick="Joomla.submitbutton('ticket.replyopen');return false;"><i class="icon-checkbox-unchecked icon-white"></i> <?php echo JText::_('OBHELPDESK_REPLY_OPEN'); ?></a></li>
						    	<?php else:?>
						    	<li><a href="#" onclick="Joomla.submitbutton('ticket.replyonhold');return false;"><i class="icon-checkbox icon-white"></i> <?php echo JText::_('OBHELPDESK_REPLY_HOLD'); ?></a></li>
						    	<?php endif;?>
						    	<li><a href="#" onclick="Joomla.submitbutton('ticket.replyclose');return false;"><i class="icon-locked icon-white"></i> <?php echo JText::_('OBHELPDESK_REPLY_CLOSE'); ?></a></li>
						    </ul>
						</div>
					</td>
				</tr>
				<?php endif;?>
			<?php endif;?>
			</table>

			<!-- REPLIES -->
			<div class="obhd_messages_wrap" id="obhd_messages_wrap">
			<?php 
			$i = 1;
			foreach ( $this->messages as $msg ) {
// 				echo '<pre>'.print_r( $msg, true ).'</pre>';
				$uname 			= ($msg->uname)? $msg->uname : $msg->cname;
				$umail 			= ($msg->umail)? $msg->umail : $msg->cmail;
				$avatar 		= obHelpDeskUserHelper::getProfileAvatar($msg->user_id, 24);
				$profile_link	= obHelpDeskUserHelper::getProfileLink($msg->user_id);
				$href = $isJ25?"":"#ob_msg_".$msg->id;
				$in = ($i==1)? ' ob_show':'';
// 				$org_content = obHelpDeskHelper::bbcodeToHtml($msg->content);
				$org_content = $msg->content;
				$raw_content = mb_substr(strip_tags($org_content), 0, 300 );
				$class_hasfiles = ($msg->files)?' obhd_hasfiles':'';

?>
					<div class="obhd_message_wrap<?php echo $in.$class_hasfiles; ?>" id="obhd_messsage_<?php echo $msg->id;?>">
						<div class="message_heading" onClick="obHDToogleMessage(<?php echo $msg->id;?>)">
							<table class="table">
								<tbody>
									<tr>
										<td class="obhd_heading_left">
											<div class="obhd_heading_left_content">
											<?php
												#TODO: load avata plugins
												if($avatar){
													echo '<div class="obhelpdesk-message-info-avatar">
															<img class="" src="'.$avatar.'" alt="'.$uname.'" title="'.$uname.'" height="48" class="hasTip" />
															</div>';
												}

												#TODO: display username
												echo '<span class="obhd_username">'.$uname.'</span>';
												
												#TODO: trigger plugin
												echo '<span class="obhd_message_plugins">';
												JPluginHelper::importPlugin('obhelpdesk');
												$dispatcher = JDispatcher::getInstance();
												$results = $dispatcher->trigger('onLoadReply', array(&$msg));
												echo '</span>';
												
												#TODO: display raw message content
												echo '<span class="obhd_raw_content">'.$raw_content.'</span>';
												
											?>
											</div>
										</td>
										<td class="obhd_heading_right">
										<?php 
										if( $msg->files ) {
											echo '<span class="icon-flag-2 pull-left"></span>';
										}
										?>
											<span class="obhd_reply_time"><?php echo obHelpDeskHelper::facebookTime($msg->reply_time);?></span>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="message_body" id="obhd_message_body<?php echo $msg->id;?>">
							<div class="org_message_body"><?php echo $org_content;?></div>
							<?php if($msg->files):?>
							<div class="obhelpdesk-message-attachments">
								<hr />
									<?php $arr_files = explode("\n", $msg->files); ?>
									<?php 
									for ($j = 0; $j < count($arr_files); $j++) {
										$file 		= $arr_files[$j];
										$time 		= JFactory::getDate($msg->reply_time)->format('YmdHis');
										$filepath 	= JPATH_COMPONENT.DS.'uploads'.DS.$time.$arr_files[$j];
										if( file_exists($filepath) ): 
									?>
										<a target="_blank" href="<?php echo JRoute::_('index.php?option=com_obhelpdesk&task=ticket.download&msg_id='.$msg->id.'&file='.base64_encode($filepath));?>"><i class="icon-download-alt"></i><?php echo $file; ?></a>
										<small>(<?php echo intval(filesize($filepath)/1024); ?> Kb)</small>
										<br/>
									<?php 
										endif;
									}
									?>
							</div>
							<?php endif;?>
						</div>
					</div>
<?php 
				++$i;
			}
?>
			</div>
<?php 
		$count_msgs = count( $this->messages );
		if( $this->messages[$count_msgs-1]->id != $this->item->first_msg_id ){
?>
			<div id="obhd_load_more_messages" class="obhd_load_more_messages" onClick="obhd_load_more_message();">
				<div ></div>
				...<br />
				<span><?php echo JText::_('COM_OBHELPDESK_LOAD_MORE')?></span>
			</div>
<?php
		}
/*			
			$i = 1;
			foreach ( $this->messages as $msg ) :
				$uname 			= ($msg->uname)? $msg->uname : $msg->cname;
				$umail 			= ($msg->umail)? $msg->umail : $msg->cmail; 
				$avatar 		= obHelpDeskUserHelper::getProfileAvatar($msg->user_id, 48);
				$profile_link	= obHelpDeskUserHelper::getProfileLink($msg->user_id);
			?>
			<tr>
				<td colspan="2" <?php if($this->email == $umail):?> onmouseover="document.getElementById('obhelpdesk-msg-icon-<?php echo $msg->id;?>').style.display='';" onmouseout="document.getElementById('obhelpdesk-msg-icon-<?php echo $msg->id;?>').style.display='none';" <?php endif;?>>
					<div class="head-msg-info" style="position: relative;">
						<?php if ($avatar!='') : ?>
						<div class="obhelpdesk-message-info-avatar">
							<img class="img-polaroid" src="<?php echo $avatar ?>" alt="<?php echo $uname;?>" title="<?php echo $uname;?>" height="48" class="hasTip" />
						</div>
						<?php endif; ?>
						<div class="obhelpdesk-message-plugins form-inline">
						<?php
						JPluginHelper::importPlugin('obhelpdesk');
						$dispatcher = JDispatcher::getInstance();
						$results = $dispatcher->trigger('onLoadReply', array(&$msg));
						?>
						</div>
						<div class="obhelpdesk-message-info-contact">
							<a href="#"><?php echo $uname;?></a>
							<small><?php echo JText::_('COM_OBHELPDESK_WROTE') . ' ' . obHelpDeskHelper::facebookTime($msg->reply_time); ?></small>
							<?php if($this->email == $umail):?>
							<div class="form-inline obhelpdesk-msg-icon" style="display: none;" id="obhelpdesk-msg-icon-<?php echo $msg->id;?>">
								<a class="btn btn-small btn-primary" title="<?php echo JText::_('Edit Message');?>" onclick="document.getElementById('bbcode-msg-<?php echo $msg->id?>').style.display=''; document.getElementById('no-bbcode-msg-<?php echo $msg->id;?>').style.display='none';"><i class="icon-edit icon-white">&nbsp;</i></a>
								<?php if($i < count($this->messages)):?>
								<a class="btn btn-small btn-danger" title="<?php echo JText::_('Delete Message');?>" onclick="return obHelpDeskValidate('ticket.delmsg', <?php echo $msg->id?>)"><i class="icon-trash icon-white">&nbsp;</i></a>
								<?php endif;?>
							</div>
							<?php endif;?>
						</div>
					</div>
					<div class="obhelpdesk-message-content">
						<div id="no-bbcode-msg-<?php echo $msg->id;?>">
						<?php echo obHelpDeskHelper::bbcodeToHtml($msg->content);?>
						<?php if($msg->files):?>
						<div class="obhelpdesk-message-attachments">
							<hr />
								<?php $arr_files = explode("\n", $msg->files);?>
								<?php for ($j = 0; $j < count($arr_files); $j++) {
									$file = $arr_files[$j];
									$time = JFactory::getDate($msg->reply_time)->toFormat('%Y%m%d%H%M%S');
									$filepath = JPATH_COMPONENT.DS.'uploads'.DS.$time.$arr_files[$j];
									?>
									<?php if(file_exists($filepath)):?>
									<a target="_blank" href="<?php echo JRoute::_('index.php?option=com_obhelpdesk&task=ticket.download&msg_id='.$msg->id.'&file='.base64_encode($filepath));?>"><i class="icon-download-alt"></i><?php echo $file; ?></a>
									<small>(<?php echo intval(filesize($filepath)/1024); ?> Kb)</small>
									<br/>
									<?php endif;?>
									<?php
								}
								?>
						</div>
						<?php endif;?>
						</div>
						<?php if($this->email == $umail):?>
						<div id="bbcode-msg-<?php echo $msg->id?>" style="display:none;">
							<div id="alarm-obhelpdesk-edit-content-msg-<?php echo $msg->id?>"><?php echo $this->bbcode->display("obhelpdesk-edit-content-msg-".$msg->id, $msg->content); ?></div>
							<div class="form-inline obhelpdesk-btn-below-editor">
								<a class="btn btn-small" onclick="return obHelpDeskValidate('ticket.updatemsg', <?php echo $msg->id?>)"><?php echo JText::_('COM_OBHELPDESK_UPDATE');?></a>
								<a class="btn btn-small" onclick="document.getElementById('bbcode-msg-<?php echo $msg->id?>').style.display='none'; document.getElementById('no-bbcode-msg-<?php echo $msg->id?>').style.display=''; return false;"><?php echo JText::_('COM_OBHELPDESK_CANCEL');?></a>
							</div>
						</div>
						<?php endif;?>
					</div>
				</td>
			</tr>
			<?php 
				$i ++;
			endforeach;
*/
			?>
		<div>
			<input type="hidden" name="task" value="ticket.reply" />
			<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
			<input type="hidden" name="obhelpdesk_msg_id" id="obhelpdesk_msg_id" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>

<script type="text/javascript">
	function obHelpDeskValidate(button, msgid) {
		body_id = 'ticket_message';
		if(button == 'ticket.remove' ||  button == 'ticket.close' || button == 'ticket.reopen' || button == 'ticket.delmsg' || button == 'ticket.updatemsg') {
    		cmd  = confirm('<?php echo JText::_('Are you sure?');?>');
    		if(cmd == true) {
        		if((button == 'ticket.delmsg' || button == 'ticket.updatemsg') && msgid > 0) {
            		document.getElementById('obhelpdesk_msg_id').value = msgid;
        		}

        		if(button == 'ticket.updatemsg') {
            		body_id = 'obhelpdesk-edit-content-msg-' + msgid;
            		rte_id = 'rte_'+body_id;
            		ifm = document.getElementById(rte_id);
            		myeditor = ifm.contentWindow.document;
            		doCheck();
    	        	var message = trim(document.getElementById(body_id).value);
    	        	// check content
    	        	alarm_id = 'alarm-obhelpdesk-edit-content-msg-' + msgid;
    	        	if( !message )
    	        	{
    	        		document.getElementById(alarm_id).setAttribute("class", 'requried invalid');
    	        		document.getElementById(alarm_id).setAttribute("aria-invalid", 'true');
    	        		return false;
    	        	} else {
    	        		document.getElementById(alarm_id).setAttribute("class", 'required');
    	        		document.getElementById(alarm_id).setAttribute("aria-invalid", 'false');
    	        	}
        		}
    			Joomla.submitform(button);
    		}
    		return false;
    	}
	}
    Joomla.submitbutton = function(task) {
        if (document.formvalidator.isValid(document.getElementById('adminForm'))) {
        	<?php if($this->perm_answer && $this->item->status != 'closed'):?>
            if(task == 'ticket.reply'||task == 'ticket.replyopen'||
                    task == 'ticket.replyclose'||task == 'ticket.replyonhold') {
            	body_id = 'ticket_message';
        		rte_id = 'rte_'+body_id;
        		ifm = document.getElementById(rte_id);
        		myeditor = ifm.contentWindow.document;
	        	doCheck();
	        	var message = trim(document.getElementById('ticket_message').value);
	        	// check content
	        	if( !message )
	        	{
	        		document.getElementById('obhelpdesk-ticket-message').setAttribute("class", 'requried invalid');
	        		document.getElementById('obhelpdesk-ticket-message').setAttribute("aria-invalid", 'true');
	        		return false;
	        	} else {
	        		document.getElementById('obhelpdesk-ticket-message').setAttribute("class", 'required');
	        		document.getElementById('obhelpdesk-ticket-message').setAttribute("aria-invalid", 'false');
	        	}
	        	// detect attachment keywords.
	        	<?php if($this->department->file_upload != 'no'):?>
	        		var attach = checkAttachment(message);
	        		if(attach == false) return false;
	        	<?php endif;?>
            }
            <?php endif;?>
            Joomla.submitform(task);
        } else {
            alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
            return false;
        }
    }

	function ShowDetailTicket(){
		if(document.getElementById('obhelpdesk-ticket-detail').style.display == 'none') {
			document.getElementById('obhelpdesk-ticket-detail').style.display = '';
			document.getElementById('icon-detail-ticket').setAttribute("class", "icon-contract icon-white");
			document.getElementById('show-ticket-detail-btn').setAttribute("title", "<?php echo JText::_('OBHELPDESK_HIDE_TICKET_DETAILS');?>");
		} else {
			document.getElementById('obhelpdesk-ticket-detail').style.display = 'none';
			document.getElementById('icon-detail-ticket').setAttribute("class", "icon-expand icon-white");
			document.getElementById('show-ticket-detail-btn').setAttribute("title", "<?php echo JText::_('OBHELPDESK_SHOW_TICKET_DETAILS');?>");
		}
	}

	function trim(str)
	{
		if(!str || typeof str != 'string')
			return null;
	
		return str.replace(/^[\s]+/,'').replace(/[\s]+$/,'').replace(/[\s]{2,}/,' ');
	}
	
	<?php if($this->department->file_upload != 'no'):?>
	function add_anothor_file() {
		var upload_number = document.getElementById('count_click_add').value;
		var id_rm 	= 'ob_rm_' + upload_number;
		var id_f	= 'ob_f_' + id_rm;
		var d = document.createElement("p");
		d.setAttribute("id", id_f);
	
		a = document.createElement('a');
		a.setAttribute("class", "");
		a.setAttribute("onclick", "removeElement('"+id_f+"');");
	
		d1 = document.createElement("i");
		d1.setAttribute("class", "icon-trash icon-white");
		d1.setAttribute("id", id_rm);
		
		a.appendChild(d1);
		var file = document.createElement("input");
		file.setAttribute("type", "file");
		file.setAttribute("name", "FileName[]");
		file.setAttribute("class", "input-file");
	
		d.appendChild(a);
		d.appendChild(file);
		document.getElementById("files_upload").appendChild(d);
		document.getElementById('count_click_add').value ++;
		if( parseInt(document.getElementById('count_click_add').value) > 0 ){
			document.getElementById('allowed_file_ext').style.display='';
		}
	}
	
	function removeElement(id) {
		var element = document.getElementById(id);
		element.parentNode.removeChild(element);
		document.getElementById('count_click_add').value --;
		if( parseInt(document.getElementById('count_click_add').value) <= 0 ){
			document.getElementById('allowed_file_ext').style.display='none';
		}
	}
	
	function checkAttachment(message) {
		//Check for Attachment	
		//has upload already?
		var uploadFilecount= document.getElementById('count_click_add').value;
		var hasUploadFile = false;
		if (document.getElementsByName('FileName[]').length > 0){
			for (var i=0;i<=document.getElementsByName('FileName[]').length-1;i++)
				{
					var value = document.getElementsByName('FileName[]')[i].value;
					if (value != "") hasUploadFile = true;
				}
		}
		
		if(hasUploadFile == false){
			//Parse content
			var strContent = message;
			//iFr_ticket_message.contentWindow.document.body.innerHTML;
			//var result= strContent.search(/Attach/i);
			//Check all attach keyword
			var searchstring;
			var x;
			var result;
			<?php  $utility_attachkey = isset($this->attachkey) ? $this->attachkey : '';?>
			var attachkey = "<?php echo $utility_attachkey; ?>";
			if(attachkey.length !=0) {
				var attachkeyList = attachkey.split(",");
				var k = attachkeyList.length-1;
				
				for(var x =0; x < attachkeyList.length; x++){
					searchstring = attachkeyList[x].toLowerCase();
					strContent = strContent.toLowerCase();
					result = strContent.indexOf(searchstring);
					if(result > -1){
						var answer = confirm ("<?php echo JText::_('FORGET_ATTACHMENT_MSG'); ?>");
						if (answer){
							add_anothor_file();
							return false;
						}else{
							break;
						}
					}
					if(x==k) {
						break;
					}
				}
			}
		}else{
			// valid extensions.
			var strAllowExtension = "<?php echo $this->department->file_upload_extensions;?>";
				
			for (var i=0;i<=document.getElementsByName('FileName[]').length-1;i++)
			{
				var value = document.getElementsByName('FileName[]')[i].value;
				var fileExt = value.split('.').pop();
				var result = strAllowExtension.indexOf(fileExt);
				if(result > -1){
				}else{
					alert("<?php echo addslashes(JText::_('DEPARTMENT_NOT_ALLOW_UPLOAD')); ?>");
					return false;
				}
			}
		}
	
		return true;
	}
	
	<?php endif;?>
	initEditor("ticket_message", true);
</script>