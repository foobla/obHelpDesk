<?php
/**
* @package		$Id: default_info.php 31 2013-08-17 04:33:28Z phonglq $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

global $option;
?>
	<table class="table table-striped">
		<tr>
			<td valign="top"><strong>Installed Version</strong></td>
			<td><strong><?php echo obHelpDeskHelper::getVersion(); ?></strong></td>
		</tr>
		
		<tr>
			<td valign="top"><strong>Copyright</strong></td>
			<td>(C) 2007-2013 <a href="http://foobla.com" target="_blank">foobla.com</a>.</td>
		</tr>
		
		
		<tr>
			<td valign="top"><strong>License</strong></td>
			<td>GNU/GPL.</td>
		</tr>
		
		<tr>
			<td valign="top"><strong>Credits</strong></td>
			<td>
				<ul style="margin: 0; padding-left: 15px;">
					<li><strong>Thong Tran</strong></li>
					<li><strong>Kien Nguyen Trung</strong></li>
					<li><strong>Kien Nguyen Van</strong></li>
					<li><strong>Phong Lo Quoc</strong></li>
				</ul>
			</td>
		</tr>
	</table>