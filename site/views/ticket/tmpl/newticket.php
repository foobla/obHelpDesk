<?php
/**
* @package		$Id: newticket.php 103 2013-12-18 10:42:49Z thongta $
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
// exit( ''.__LINE__ );
$params = $this->form->getFieldsets('params');
$did = JRequest::getVar('did');
$option = JRequest::getVar('option');
$document = JFactory::getDocument();
# set page title
$document->setTitle( JText::_('COM_OBHELPDESK_NEWTICKET'));
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (document.formvalidator.isValid(document.getElementById('adminForm'))) {
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

			// check re-captcha
			<?php if ($this->recaptcha->enabled) : ?>
			if (document.getElementById('adminForm').recaptcha_response_field) {
				if (document.getElementById('adminForm').recaptcha_response_field.value == "") {
					document.getElementById('adminForm').recaptcha_response_field.setAttribute("class", 'required invalid');
					document.getElementById('adminForm').recaptcha_response_field.setAttribute("aria-invalid", 'true');
					Recaptcha.reload();
					return false;
				} else {
					document.getElementById('adminForm').recaptcha_response_field.setAttribute("aria-invalid", 'false');
				}
			}
			<?php endif; ?>
			Joomla.submitform(task);
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
			return false;
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



	window.addEvent('domready', function() {
		$('jform_subject').addEvent('change', function(){
			ifm = document.id('rte_ticket_message');
			ifm.contentWindow.focus();
			var str = this.value;
			if(str == ''){
				$('ob_faqs').innerHTML = '';
			} else {
				//var department = document.getElementById('department_id').value;
				var department = <?php echo $did;?>;
				var url = 'index.php?option=<?php echo $option;?>&task=ticket.loadfaq&str='+str+'&department='+department+'&tmpl=component';
				var myAjax = new Request({
					url:url,
					method: 'get',
					evalScripts: true,
					onSuccess: function(responseText, responseXML) {
						if ( responseText!= '' ) {
							document.getElementById('ob_tr_faqs').style.display='table-row';
							document.getElementById('ob_tr_faqs_container').style.display='table-row';
							document.getElementById('ob_faqs').innerHTML = responseText;
<?php
				if($isJ25){
					echo '
							new Accordion(
									$$( "#ob_faqs .accordion a.accordion-toggle" ),
									$$( "#ob_faqs .accordion div.accordion-body" ),
									{
										onActive: function(toggler, i) {
											toggler.addClass("ob_arrow_down");
										},
										onBackground: function(toggler, i) {
											toggler.addClass("accordion-toggle");
										},
										duration: 300,opacity: false,alwaysHide: true
									}
								);';
				} else {
							echo 'jQuery("#ob_faqs .accordion .collapse").collapse();';
				}
?>
						} else {
							document.getElementById('ob_tr_faqs').style.display='none';
							document.getElementById('ob_tr_faqs_container').style.display='none';
							document.getElementById('ob_faqs').innerHTML = '';
						}
					}
				}).send();
			}
		});

	});

</script>
<div id="foobla">
	<?php
	require JPATH_COMPONENT.DS.'helpers'.DS.'menu.php';
	$menu = new obHelpDeskMenuHelper();
	$menu->topnav('newticket');
	?>
	<legend>You are submitting ticket to "<?php echo $this->department->title;?>" department</legend>

	<?php
	echo obHelpDeskHelper::loadAnnouncements('newticket');
	?>

	<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_obhelpdesk'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal" role="form">
		<table class="table table-bordered">
			<!--  custom fields of departments -->
			<?php if(count($this->fields)) :?>
			<?php foreach ($this->fields as $field) :?>
			<tr>
				<td style="text-align: right;"><label id="jform_obhelpdesk_<?php echo $field->id?>-lbl" for="jform_obhelpdesk_<?php echo $field->id?>" class="hasTip" title="<?php echo $field->title;?>::<?php echo $field->helptext;?>" ><?php echo $field->title; if($field->required) echo ' *'?></td>
				<td>
					<?php
// 					var_dump($field);
					if(isset($this->data)) {
// 						echo __LINE__;
						if(isset($this->data['field'][$field->id])) $field->default_value = is_array($this->data['field'][$field->id]) ? implode('|', $this->data['field'][$field->id]) : $this->data['field'][$field->id];
					}
					if(isset($_REQUEST['jform']['field'][$field->id])&&$_REQUEST['jform']['field'][$field->id] ){
						$field->default_value = $_REQUEST['jform']['field'][$field->id];
					}
					echo obHelpDeskFieldsHelper::printField($field);
					?>
				</td>
			</tr>
			<?php endforeach;?>
			<?php endif;?>
			<tr>
				<td colspan="2"><strong><?php echo JText::_('COM_OBHELPDESK_GENERAL_INFO');?></strong></td>
			</tr>
			<?php if($this->is_staff) : // STAFF ?>
				<?php
					$userid = null;
					if(isset($this->data)) {
						if(isset($this->data['user_id'])) $userid = $this->data['user_id'];
					}
				?>
				<?php if($this->add_ticket_users && $this->add_ticket_staffs):?>
				<tr>
					<td style="text-align: right;"><label id="jform_obhelpdesk_user_id-lbl" for="jform_obhelpdesk_user_id">Select User</label></td>
					<td><?php echo $this->form->getInput('user_id' , null, $userid);?></td>
				</tr>
				<?php elseif($this->add_ticket_users):?>
				<tr>
					<td style="text-align: right;"><label id="jform_obhelpdesk_user_id-lbl" for="jform_obhelpdesk_user_id">Select User</label></td>
					<td><?php echo $this->form->getInput('customer_id' , null, $userid);?></td>
				</tr>
				<?php elseif($this->add_ticket_staffs):?>
				<tr>
					<td style="text-align: right;"><label id="jform_obhelpdesk_user_id-lbl" for="jform_obhelpdesk_user_id">Select User</label></td>
					<td><?php echo $this->form->getInput('staff_id' , null, $userid);?></td>
				</tr>
				<?php endif;?>
			<?php endif; ?>
			<?php if(!$this->user->id) : // GUEST ?>
			<?php
			 	$fullname = null;
			 	$email = null;
				if(isset($this->data)) {
					if(isset($this->data['fullname'])) $fullname = $this->data['fullname'];
					if(isset($this->data['email'])) $email = $this->data['email'];
				}
			?>
			<tr>
				<td style="text-align:right"><label id="jform_obhelpdesk_fullname" for="jform_obhelpdesk_fullname">Full Name *</label></td>
				<td><?php echo $this->form->getInput('fullname' , null, $fullname);?></td>
			</tr>
			<tr>
				<td style="text-align:right"><label id="jform_obhelpdesk_email" for="jform_obhelpdesk_email">E-mail *</label></td>
				<td>
					<?php echo $this->form->getInput('email' , null, $email);?>
					<p class="help-block"><?php echo JText::_('COM_OBHELPDESK_EMAIL_HELP'); ?>
				</td>
			</tr>
			<?php endif;?>
			<?php
			 	$priority = $this->department->priority;
			 	$subject = null;
				if(isset($this->data)) {
					if(isset($this->data['priority'])) $priority = $this->data['priority'];
					if(isset($this->data['subject'])) $subject = $this->data['subject'];
				}
			?>
			<tr>
				<td style="text-align:right"><label id="jform_obhelpdesk_priority" for="jform_obhelpdesk_priority"><?php echo JText::_('COM_OBHELPDESK_PRIORITY'); ?> *</label></td>
				<td><?php echo $this->form->getInput('priority', null, $priority);?></td>
			</tr>
			<tr>
				<td colspan="2"><strong><?php echo JText::_('COM_OBHELPDESK_MESSAGE_DETAILS');?></strong></td>
			</tr>
			<tr>
				<td style="text-align:right"><label id="jform_obhelpdesk_subject" for="jform_obhelpdesk_subject"><?php echo JText::_('COM_OBHELPDESK_SUBJECT'); ?> *</label></td>
				<td><?php echo $this->form->getInput('subject', null, $subject);?>
					<p class="help-block"><?php echo JText::_('COM_OBHELPDESK_SUBJECT_HELP'); ?></p>
				</td>
			</tr>


			<!-- FAQ -->
			<tr id="ob_tr_faqs" style="display:none">
				<th colspan="2"><?php echo JText::_( 'COM_OBHELPDESK_FAQ_INFO' ); ?></th>
			</tr>
			<tr id="ob_tr_faqs_container" style="display: none">
				<td id="ob_faqs" colspan="2">&nbsp;</td>
			</tr>
			<!-- END FAQ -->

			<tr>
				<td colspan="2">
					<div id="obhelpdesk-ticket-message"><?php echo $this->editor_message; ?></div>

					<p class="help-block"><?php echo JText::_('COM_OBHELPDESK_TICKET_MESSAGE_HELP'); ?>

					<?php if($this->department->file_upload != 'no'):?>
					<p>
					<strong><span id="add_anothor_file" onclick="add_anothor_file();"><i class="icon icon-flag-2"></i><?php echo JText::_('OBHELPDESK_ADD_ATTACHMENT'); ?></span></strong>
					<br />
					<div id="files_upload">
					<span class="label" id="allowed_file_ext" style="display:none;"><?php echo JText::_('OBHELPDESK_ALLOWED_FILE_EXT'); ?> <?php echo $this->department->file_upload_extensions;?></span>
					</div>

					<input type="hidden" name="count_click_add" id="count_click_add" value="0" onchange="">
					</p>
					<?php endif;?>
				</td>
			</tr>

			<?php if (!$this->user->id && $this->recaptcha->enabled) : ?>
			<tr>
				<td colspan="2">

				<!-- Word Verification -->
				<table class="obhelpdesk-table" width="100%" cellpadding="4" cellspacing="1" border="0">
					<tr>
						<td width="30%" class="obhelpdesk-label"><strong><?php echo JText::_('WORD_VERIFICATION')?></strong></td>
						<td>
						<script type="text/javascript">
						<!--
						var RecaptchaOptions = {
							theme : 'clean'
						};
						//-->
						</script>
						<?php
							echo recaptcha_get_html($this->recaptcha->publickey, $this->recaptcha->error);
						?>
						</td>
					</tr>
				</table>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td colspan="2"><button type="button" class="btn btn-primary" onClick="return Joomla.submitbutton('ticket.newticket')"><i class="icon-chevron-right icon-white"></i><?php echo JText::_('COM_OBHELPDESK_SEND_TICKET');?></button></td>
			</tr>
		</table>
		<div>
			<input type="hidden" name="task" value="ticket.newticket" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
<script>
	initEditor("ticket_message", true);
</script>