<?php
/**
 * @package        $Id: obhelpdesk.php 103 2013-12-18 10:42:49Z thongta $
 * @author         foobla.com
 * @copyright      2007-2014 foobla.com. All rights reserved.
 * @license        GNU/GPL.
 */

// no direct access
defined( '_JEXEC' ) or die;


class obHelpDeskHelper {

	protected static $actions;
	public static $fields;


	/**
	 * Gets a list of the actions that can be performed.
	 */
	public static function getActions( $categoryId = 0, $articleId = 0 ) {
		if ( empty( self::$actions ) ) {
			$user          = JFactory::getUser();
			self::$actions = new JObject;

			$actions = array(
				'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
			);

			foreach ( $actions as $action ) {
				self::$actions->set( $action, $user->authorise( $action, 'com_obhelpdesk' ) );
			}
		}

		return self::$actions;
	}

	/**
	 * load Config by name
	 */
	public static function getConfig( $name = null ) {
		$params     = JComponentHelper::getParams( 'com_obhelpdesk' );
		$obj        = new stdClass();
		$obj->value = $params->get( $name );

		return $obj;
	}

	public static function getFullConfig() {
		$params = JComponentHelper::getParams( 'com_obhelpdesk' );

		return $params;
	}

	public static function reCaptchaProcess() {
		$app = JFactory::getApplication();
		require_once JPATH_COMPONENT . DS . 'helpers' . DS . 'recaptchalib.php';
		$privatekey = self::getConfig( 'utility_recaptcha_privatekey' )->value;
		$resp       = recaptcha_check_answer(
			$privatekey,
			$_SERVER["REMOTE_ADDR"],
			$_POST["recaptcha_challenge_field"],
			$_POST["recaptcha_response_field"]
		);

		if ( ! $resp->is_valid ) {
			$link      = $_SERVER['HTTP_REFERER'];
			$msg_error = JText::_( 'ERROR_SECURITY_CODE' );
			$app->enqueueMessage( $msg_error, 'error' );
			$app->redirect( $link );
		}
	}

	public static function is_email( $email ) {
		return preg_match( '|^[_a-z0-9._%+-]+(\.[_a-z0-9-]+)*@[a-z0-9.-]+(\.[a-z0-9-]{2,4})+$|i', $email );
	}

	public static function generateQuickCode( $datetime ) {
		$time       = JFactory::getDate( $datetime )->format( 'YmdHis' );
		$db         = JFactory::getDbo();
		$query_code = '
			SELECT MAX(`id`)
			FROM `#__obhelpdesk3_tickets`
		';
		$db->setQuery( $query_code );
		$maxID = intval( $db->loadResult() );

		return $maxID . $time;
	}

	public static function saveFields( $fields, $ticketID, $did, $update = false ) {
		$db = JFactory::getDbo();
		if ( ! count( $fields ) ) {
			return true;
		}

		$arr_values = array();
		foreach ( $fields as $key => $field ) {
			if ( is_array( $field ) ) { // Multiple select field
				foreach ( $field as $v ) {
					$arr_values[] = "(" . $key . ", " . $ticketID . ", '" . addslashes( $v ) . "', " . $did . ")";
				}
			} else {
				$arr_values[] = "(" . $key . ", " . $ticketID . ", '" . addslashes( $field ) . "', " . $did . ")";
			}
		}
		// insert custom field values to database
		$values = implode( ', ', $arr_values );
		if ( $update ) {
			$sql = "DELETE FROM `#__obhelpdesk3_field_values` WHERE `ticket_id`=" . $ticketID;
			$db->setQuery( $sql );
			if ( ! $db->query() ) {
				echo $db->getErrorMsg();

				return false;
			}
		}
		$sql = "INSERT INTO `#__obhelpdesk3_field_values`(`field_id`, `ticket_id`, `value`, `department_id`)";
		$sql .= "VALUES " . $values;
		$db->setQuery( $sql );
		if ( ! $db->query() ) {
			echo $db->getErrorMsg();

			return false;
		}

		return true;
	}

	public static function getFields( $tid, $did ) {
		$db    = JFactory::getDbo();
		$query = "SELECT f.`id`, f.`name`, v.`value`, f.`values`"
			. " FROM `#__obhelpdesk3_fields` as f,"
			. " `#__obhelpdesk3_field_values` as v"
			. " WHERE f.`id` = v.`field_id` AND `ticket_id`=" . $tid . " AND `department_id`=" . $did
			. " ORDER BY `f`.`ordering` ASC";
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		if ( count( $rows ) ) {
			foreach ( $rows as $row ) {
				if ( $row->values ) {
					$arr = explode( "\n", $row->values );
					if ( count( $arr ) ) {
						foreach ( $arr as $v ) {
							$ex = explode( ":", $v );
							if ( count( $ex ) == 1 ) {
								$ex[1] = $ex[0];
							}
							if ( $ex[0] == $row->value ) {
								$row->value = $ex[1];
							}
						}
					}
				}

			}
		}

		return $rows;
	}

	public static function getEmailTemplate( $type ) {
		$db    = JFactory::getDbo();
		$query = "SELECT * FROM `#__obhelpdesk3_emailtemplates` WHERE `type` = '" . $type . "'";
		$db->setQuery( $query );

		return $db->loadObject();
	}

	public static function SendMail( $type, $emailto, $fullname, $message, $ticket, $attachment, $arg = array() ) {
		$mode = true; // HTML
		$mail = JFactory::getMailer();


		// get Configs from configuration page of obhelpdesk 
		$params = JComponentHelper::getParams( 'com_obhelpdesk' );
		// get global configuration.
		$config = JFactory::getConfig();

		$cfg_use_global = (int) $params->get( 'hdemail_use_global' );
		$from           = $params->get( 'hdemail_address' );
		$fromname       = $params->get( 'hdemail_address_fullname' );

		if ( $cfg_use_global == 1 ) {
			$fromname = $config->get( 'fromname' );
			$from     = $config->get( 'mailfrom' );
		} elseif ( $cfg_use_global == 2 ) {
			// get config from cron mail settings.
			$mailer     = $params->get( 'mailer' );
			$smtpauth   = ( $params->get( 'smtpauth' ) == 0 ) ? null : 1;
			$smtpuser   = $params->get( 'smtpuser' );
			$smtppass   = $params->get( 'smtppass' );
			$smtphost   = $params->get( 'smtphost' );
			$smtpsecure = $params->get( 'smtpsecure' );
			$smtpport   = $params->get( 'smtpport' );
			$mailfrom   = $params->get( 'mailfrom' );
			$fromname   = $params->get( 'fromname' );
			$from       = $mailfrom;
			// Default mailer is to use PHP's mail function
			switch ( $mailer ) {
				case 'smtp':
					$mail->useSMTP( $smtpauth, $smtphost, $smtpuser, $smtppass, $smtpsecure, $smtpport );
					break;
				case 'sendmail':
					$mail->IsSendmail();
					break;
				default:
					$mail->IsMail();
					break;
			}
		}

		$submit_without_login = $params->get( 'submit_without_login' );


		$template       = self::getEmailTemplate( $type );
		$ticket_message = $arg['message'];
		$department     = $arg['department'];
		$staff          = null;
		$staff          = isset( $arg['staff'] ) ? $arg['staff'] : null;

		$ticket_url = JRoute::_( JURI::base() . 'index.php?option=com_obhelpdesk&task=ticket.viewdetail&id=' . $ticket->id );
		if ( $submit_without_login && $emailto == $ticket->customer_email ) {
			$ticket_url .= '&email=' . $ticket->customer_email . '&quickcode=' . $ticket->quickcode;
		}


		$keys = self::getKeywords( $template->subject . ' ' . $template->message );
		if ( ( in_array( '{staff_name}', $keys ) || in_array( '{staff_email}', $keys ) ) && $ticket->staff && ! $staff ) {
			$staff = JFactory::getUser( $ticket->staff );
		}

		$fromuser = null;
		if ( in_array( '{message_fromname}', $keys ) || in_array( '{message_email}', $keys ) ) {
			$msg      = isset( $arg['message'] ) ? $arg['message'] : '';
			$fromuser = ( $msg ) ? JFactory::getUser( $msg->user_id ) : JFactory::getUser();
			if ( $fromuser->guest ) {
				$fromuser->name  = $ticket->customer_fullname;
				$fromuser->email = $ticket->customer_email;
			}
		}


		$ticket_subject = $ticket->subject;
		$site_url       = JURI::base();
		$site_name      = $config->get( 'sitename' );
		$ticket_code    = $department->prefix . '-' . $ticket->id;

		#################
		# SUBJECT
		#################
		$email_subject = $template->subject;
		$keys          = self::getKeywords( $email_subject );

		$values = array();
		if ( in_array( '{site_name}', $keys ) ) {
			$values['{site_name}'] = $sitename;
			$email_subject         = str_replace( '{site_name}', $sitename, $email_subject );
		}


		if ( in_array( '{site_url}', $keys ) ) {
			$values['{site_url}'] = $site_url;
			$email_subject        = str_replace( '{site_url}', $site_url, $email_subject );
		}

		if ( in_array( '{ticket_subject}', $keys ) ) {
			$values['{ticket_subject}'] = $site_url;
			$email_subject              = str_replace( '{ticket_subject}', $ticket->subject, $email_subject );
		}

		if ( in_array( '{ticket_code}', $keys ) ) {
			$values['{ticket_code}'] = $ticket_code;
			$email_subject           = str_replace( '{ticket_code}', $ticket_code, $email_subject );
		}

		if ( ! $email_subject ) {
			$email_subject = $subject;
		}

		#################
		# BODY
		#################
		$email_body = $template->message;
		$keys       = self::getKeywords( $email_body );

		if ( in_array( '{site_name}', $keys ) ) {
			$email_body = str_replace( '{site_name}', $site_name, $email_body );
		}

		if ( in_array( '{site_url}', $keys ) ) {
			$email_body = str_replace( '{site_url}', $site_url, $email_body );
		}

		if ( in_array( '{ticket_code}', $keys ) ) {
			$email_body = str_replace( '{ticket_code}', $ticket_code, $email_body );
		}

		if ( in_array( '{ticket_url}', $keys ) ) {
			$email_body = str_replace( '{ticket_url}', $ticket_url, $email_body );
		}

		if ( in_array( '{ticket_subject}', $keys ) ) {
			$email_body = str_replace( '{ticket_subject}', $ticket->subject, $email_body );
		}

		if ( in_array( '{staff_name}', $keys ) && $ticket->staff ) {
			$email_body = str_replace( '{staff_name}', $staff->name, $email_body );
		}


		if ( in_array( '{staff_email}', $keys ) && $ticket->staff ) {
			$email_body = str_replace( '{staff_email}', $staff->email, $email_body );
		}


		if ( in_array( '{customer_name}', $keys ) ) {
			$email_body = str_replace( '{customer_name}', $ticket->customer_fullname, $email_body );
		}

		if ( in_array( '{customer_email}', $keys ) ) {
			$email_body = str_replace( '{customer_email}', $ticket->customer_email, $email_body );
		}

		if ( in_array( '{message_fromname}', $keys ) && $fromuser ) {
			$email_body = str_replace( '{message_fromname}', $fromuser->name, $email_body );
		}

		if ( in_array( '{message_fromemail}', $keys ) && $fromuser ) {
			$email_body = str_replace( '{message_fromemail}', $fromuser->email, $email_body );
		}

		if ( in_array( '{overdue_time}', $keys ) ) {
			$overdue    = $params->get( 'overduetime' );
			$overdue    = intval( $overdue / 60 );
			$email_body = str_replace( '{overdue_time}', $overdue, $email_body );
		}


		# CUSTOME FIELD
		if ( in_array( '{custom_fields}', $keys ) ) {

			$fields        = self::getFields( $ticket->id, $ticket->departmentid );
			$custom_fields = array();

			$i = 0;
			foreach ( $fields AS $field ) {
				if ( isset( $custom_fields[$field->id] ) ) {
					$custom_fields[$field->id] .= ', ' . $field->value;
				} else {
					$custom_fields[$field->id] = $field->name . ': ' . $field->value;
				}
				$i ++;
			}

			$print_fields = implode( '<br />', $custom_fields );
			$print_fields .= '<hr />';
			$email_body = str_replace( '{custom_fields}', $print_fields, $email_body );

		}


		# MESAGE BODY
		if ( in_array( '{message_body}', $keys ) ) {
			$email_body = str_replace( '{message_body}', $ticket_message->content, $email_body );
		}


		$body_top = '<html><body lang="en" style="background-color:#fff; color: #222">';
		$body_bot = '</body></html>';
		$body     = $body_top . $email_body . $body_bot;

		if ( $mail->sendMail( $from, $fromname, $emailto, $email_subject, $body, $mode, null, null, $attachment ) != true ) {
			JError::raiseNotice( 500, "Email can not be sent > " . __LINE__ . " > $from > $fromname > $email > $email_subject" );
		}

	}

	public static function bbcodeToHtml( $bbcode ) {
		// example: [b] to <strong>
		$html = preg_replace( '/\</i', "&lt;", $bbcode ); //removing html tags
		$html = preg_replace( '/\>/i', "&gt;", $html );

		$html = preg_replace( '/\n/i', "<br />", $html );
		$html = preg_replace( '/\[ul\]/i', "<ul>", $html );
		$html = preg_replace( '/\[\/ul\]/i', "</ul>", $html );
		$html = preg_replace( '/\[ol\]/i', "<ol>", $html );
		$html = preg_replace( '/\[\/ol\]/i', "</ol>", $html );
		$html = preg_replace( '/\[li\]/i', "<li>", $html );
		$html = preg_replace( '/\[\/li\]/i', "</li>", $html );

		$html = preg_replace( '/\[b\]/i', "<span style=\"font-weight: bold;\">", $html );
		$html = preg_replace( '/\[i\]/i', "<span style=\"font-style: italic;\">", $html );
		$html = preg_replace( '/\[u\]/i', "<span style=\"text-decoration: underline;\">", $html );
		$html = preg_replace( '/\[\/(b|i|u)\]/i', "</span>", $html );

		$html = preg_replace( '/\[img\]([^\"]*?)\[\/img\]/i', "<img src=\"$1\" />", $html );
		$out  = $html;
		do {
			$out  = $html;
			$html = preg_replace( '/\[url=([^\]]+)\]([\s\S]*?)\[\/url\]/i', "<a href=\"$1\">$2</a>", $html );
			$html = preg_replace( '/\[url\]([\s\S]*?)\[\/url\]/i', "<a href=\"$1\">$1</a>", $html );

			$html = preg_replace( '/\[quote=([^\]]+)\]([\s\S]*?)\[\/quote\]/i', "<div class=\"obhd_quote\"><div class=\"obhd_quote_title\">$1</div>$2</div>", $html );
			$html = preg_replace( '/\[quote\]([\s\S]*?)\[\/quote\]/i', "<div class=\"obhd_quote\">$1</div>", $html );
			$html = preg_replace( '/\[quote=\]([\s\S]*?)\[\/quote\]/i', "<div class=\"obhd_quote\">$1</div>", $html );
			$html = preg_replace( '/\[color=([0-9A-F]{6})\]([\s\S]*?)\[\/color\]/i', "<span style=\"color: #$1;\">$2</span>", $html );
			$html = preg_replace( '/\[color=([^\]]*?)\]([\s\S]*?)\[\/color\]/i', "<span style=\"color: $1;\">$2</span>", $html );
			$html = preg_replace( '/\[font=([^\]]*?)\]([\s\S]*?)\[\/font\]/i', "<span style=\"font-family: $1;\">$2</span>", $html );
			$html = preg_replace( '/\[code\]([\s\S]*?)\[\/code\]/i', "<pre>$1</pre>&nbsp;", $html );
		} while ( $out != $html );

		return $html;
	}

	public static function html2bbcode( $text ) {
		$htmltags = array(
			'/\<b\>(.*?)\<\/b\>/is',
			'/\<i\>(.*?)\<\/i\>/is',
			'/\<u\>(.*?)\<\/u\>/is',
			'/\<ul.*?\>(.*?)\<\/ul\>/is',
			'/\<li\>(.*?)\<\/li\>/is',
			'/\<img(.*?) src=\"(.*?)\" alt=\"(.*?)\" title=\"Smile(y?)\" \/\>/is', // some smiley
			'/\<img(.*?) src=\"http:\/\/(.*?)\" (.*?)\>/is',
			'/\<img(.*?) src=\"(.*?)\" alt=\":(.*?)\" .*? \/\>/is', // some smiley
			'/\<div class=\"quotecontent\"\>(.*?)\<\/div\>/is',
			'/\<div class=\"codecontent\"\>(.*?)\<\/div\>/is',
			'/\<div class=\"quotetitle\"\>(.*?)\<\/div\>/is',
			'/\<div class=\"codetitle\"\>(.*?)\<\/div\>/is',
			'/\<cite.*?\>(.*?)\<\/cite\>/is',
			'/\<blockquote.*?\>(.*?)\<\/blockquote\>/is',
			'/\<div\>(.*?)\<\/div\>/is',
			'/\<code\>(.*?)\<\/code\>/is',
			'/\<br(.*?)\>/is',
			'/\<strong\>(.*?)\<\/strong\>/is',
			'/\<em\>(.*?)\<\/em\>/is',
			'/\<a href=\"mailto:(.*?)\"(.*?)\>(.*?)\<\/a\>/is',
			'/\<a .*?href=\"(.*?)\"(.*?)\>http:\/\/(.*?)\<\/a\>/is',
			'/\<a .*?href=\"(.*?)\"(.*?)\>(.*?)\<\/a\>/is'
		);

		$bbtags = array(
			'[b]$1[/b]',
			'[i]$1[/i]',
			'[u]$1[/u]',
			'[list]$1[/list]',
			'[*]$1',
			'$3',
			'[img]http://$2[/img]',
			':$3',
			'\[quote\]$1\[/quote\]',
			'\[code\]$1\[/code\]',
			'',
			'',
			'',
			'\[quote\]$1\[/quote\]',
			'$1',
			'\[code\]$1\[/code\]',
			"\n",
			'[b]$1[/b]',
			'[i]$1[/i]',
			'[email=$1]$3[/email]',
			'[url]$1[/url]',
			'[url=$1]$3[/url]'
		);

//		$text = str_replace ("\n", ' ', $text);
		$ntext = preg_replace( $htmltags, $bbtags, $text );
		$ntext = preg_replace( $htmltags, $bbtags, $ntext );

		// for too large text and cannot handle by str_replace
		if ( ! $ntext ) {
			$ntext = str_replace( array( '<br>', '<br />' ), "\n", $text );
			$ntext = str_replace( array( '<strong>', '</strong>' ), array( '[b]', '[/b]' ), $ntext );
			$ntext = str_replace( array( '<em>', '</em>' ), array( '[i]', '[/i]' ), $ntext );
		}

		$ntext = strip_tags( $ntext );
		$ntext = trim( html_entity_decode( $ntext, ENT_QUOTES, 'UTF-8' ) );

		return $ntext;
	}

	/**
	 *
	 * x- days ago format ...
	 *
	 * @param $time
	 */
	public static function facebookTime( $time ) {
		//$time			= JHTML::_('date', $time, '%Y-%m-%d %H:%M:%S');

		$chunks = array(
			array( 31570560, JText::_( 'OBHELPDESK_DATE_YEAR' ), JText::_( 'OBHELPDESK_DATE_YEARS' ) ),
			array( 2630880, JText::_( 'OBHELPDESK_DATE_MONTH' ), JText::_( 'OBHELPDESK_DATE_MONTHS' ) ),
			array( 604800, JText::_( 'OBHELPDESK_DATE_WEEK' ), JText::_( 'OBHELPDESK_DATE_WEEKS' ) ),
			array( 86400, JText::_( 'OBHELPDESK_DATE_DAY' ), JText::_( 'OBHELPDESK_DATE_DAYS' ) ),
			array( 3600, JText::_( 'OBHELPDESK_DATE_HOUR' ), JText::_( 'OBHELPDESK_DATE_HOURS' ) ),
			array( 60, JText::_( 'OBHELPDESK_DATE_MINUTE' ), JText::_( 'OBHELPDESK_DATE_MINUTES' ) ),
			array( 1, JText::_( 'OBHELPDESK_DATE_SECOND' ), JText::_( 'OBHELPDESK_DATE_SECONDS' ) )
		);
		$j      = count( $chunks );

		$time_n = strtotime( $time );
		$now    = time() - date( 'Z', time() );
		$since  = $now - $time_n;

		$returnTime = '1' . JText::_( 'OBHELPDESK_DATE_SECOND' );

		// find 1st chunk
		for ( $i = 0; $i < $j; $i ++ ) {
			$seconds = $chunks [$i] [0];

			if ( ( $count = floor( $since / $seconds ) ) != 0 ) {
				$nameOnly = $chunks [$i] [1];
				$nameMany = $chunks [$i] [2];
				// set 1st chunk output
				$returnTime = ( $count == 1 ) ? '1' . $nameOnly : $count . '' . $nameMany;
				break;
			}
		}


		// find 2nd chunk
		if ( $i + 1 < $j ) { // >=hours
			$seconds2nd = $chunks [$i + 1] [0];

			if ( ( $count2 = floor( ( $since - ( $seconds * $count ) ) / $seconds2nd ) ) != 0 ) {
				$nameOnly2 = $chunks [$i + 1] [1];
				$nameMany2 = $chunks [$i + 1] [2];
				// set 1st chunk output
				$returnTime .= ( $count2 == 1 ) ? ', 1' . $nameOnly2 : ', ' . $count2 . '' . $nameMany2;
			}
		}

		$xitinTime = '<span class="hasTip" title="' . $time . '" style="font: inherit;">' . str_replace( '%time%', $returnTime, JText::_( 'OBHELPDESK_TIME_SINCE' ) ) . '</span>';

		return $xitinTime;
	}

	public static function datei_mime( $filetype ) {

		switch ( $filetype ) {
			case "ez":
				$mime = "application/andrew-inset";
				break;
			case "hqx":
				$mime = "application/mac-binhex40";
				break;
			case "cpt":
				$mime = "application/mac-compactpro";
				break;
			case "doc":
				$mime = "application/msword";
				break;
			case "bin":
				$mime = "application/octet-stream";
				break;
			case "dms":
				$mime = "application/octet-stream";
				break;
			case "lha":
				$mime = "application/octet-stream";
				break;
			case "lzh":
				$mime = "application/octet-stream";
				break;
			case "exe":
				$mime = "application/octet-stream";
				break;
			case "class":
				$mime = "application/octet-stream";
				break;
			case "dll":
				$mime = "application/octet-stream";
				break;
			case "oda":
				$mime = "application/oda";
				break;
			case "pdf":
				$mime = "application/pdf";
				break;
			case "ai":
				$mime = "application/postscript";
				break;
			case "eps":
				$mime = "application/postscript";
				break;
			case "ps":
				$mime = "application/postscript";
				break;
			case "xls":
				$mime = "application/vnd.ms-excel";
				break;
			case "ppt":
				$mime = "application/vnd.ms-powerpoint";
				break;
			case "wbxml":
				$mime = "application/vnd.wap.wbxml";
				break;
			case "wmlc":
				$mime = "application/vnd.wap.wmlc";
				break;
			case "wmlsc":
				$mime = "application/vnd.wap.wmlscriptc";
				break;
			case "vcd":
				$mime = "application/x-cdlink";
				break;
			case "pgn":
				$mime = "application/x-chess-pgn";
				break;
			case "csh":
				$mime = "application/x-csh";
				break;
			case "dvi":
				$mime = "application/x-dvi";
				break;
			case "spl":
				$mime = "application/x-futuresplash";
				break;
			case "gtar":
				$mime = "application/x-gtar";
				break;
			case "hdf":
				$mime = "application/x-hdf";
				break;
			case "js":
				$mime = "application/x-javascript";
				break;
			case "nc":
				$mime = "application/x-netcdf";
				break;
			case "cdf":
				$mime = "application/x-netcdf";
				break;
			case "swf":
				$mime = "application/x-shockwave-flash";
				break;
			case "tar":
				$mime = "application/x-tar";
				break;
			case "tcl":
				$mime = "application/x-tcl";
				break;
			case "tex":
				$mime = "application/x-tex";
				break;
			case "texinfo":
				$mime = "application/x-texinfo";
				break;
			case "texi":
				$mime = "application/x-texinfo";
				break;
			case "t":
				$mime = "application/x-troff";
				break;
			case "tr":
				$mime = "application/x-troff";
				break;
			case "roff":
				$mime = "application/x-troff";
				break;
			case "man":
				$mime = "application/x-troff-man";
				break;
			case "me":
				$mime = "application/x-troff-me";
				break;
			case "ms":
				$mime = "application/x-troff-ms";
				break;
			case "ustar":
				$mime = "application/x-ustar";
				break;
			case "src":
				$mime = "application/x-wais-source";
				break;
			case "zip":
				$mime = "application/x-zip";
				break;
			case "au":
				$mime = "audio/basic";
				break;
			case "snd":
				$mime = "audio/basic";
				break;
			case "mid":
				$mime = "audio/midi";
				break;
			case "midi":
				$mime = "audio/midi";
				break;
			case "kar":
				$mime = "audio/midi";
				break;
			case "mpga":
				$mime = "audio/mpeg";
				break;
			case "mp2":
				$mime = "audio/mpeg";
				break;
			case "mp3":
				$mime = "audio/mpeg";
				break;
			case "aif":
				$mime = "audio/x-aiff";
				break;
			case "aiff":
				$mime = "audio/x-aiff";
				break;
			case "aifc":
				$mime = "audio/x-aiff";
				break;
			case "m3u":
				$mime = "audio/x-mpegurl";
				break;
			case "ram":
				$mime = "audio/x-pn-realaudio";
				break;
			case "rm":
				$mime = "audio/x-pn-realaudio";
				break;
			case "rpm":
				$mime = "audio/x-pn-realaudio-plugin";
				break;
			case "ra":
				$mime = "audio/x-realaudio";
				break;
			case "wav":
				$mime = "audio/x-wav";
				break;
			case "pdb":
				$mime = "chemical/x-pdb";
				break;
			case "xyz":
				$mime = "chemical/x-xyz";
				break;
			case "bmp":
				$mime = "image/bmp";
				break;
			case "gif":
				$mime = "image/gif";
				break;
			case "ief":
				$mime = "image/ief";
				break;
			case "jpeg":
				$mime = "image/jpeg";
				break;
			case "jpg":
				$mime = "image/jpeg";
				break;
			case "jpe":
				$mime = "image/jpeg";
				break;
			case "png":
				$mime = "image/png";
				break;
			case "tiff":
				$mime = "image/tiff";
				break;
			case "tif":
				$mime = "image/tiff";
				break;
			case "wbmp":
				$mime = "image/vnd.wap.wbmp";
				break;
			case "ras":
				$mime = "image/x-cmu-raster";
				break;
			case "pnm":
				$mime = "image/x-portable-anymap";
				break;
			case "pbm":
				$mime = "image/x-portable-bitmap";
				break;
			case "pgm":
				$mime = "image/x-portable-graymap";
				break;
			case "ppm":
				$mime = "image/x-portable-pixmap";
				break;
			case "rgb":
				$mime = "image/x-rgb";
				break;
			case "xbm":
				$mime = "image/x-xbitmap";
				break;
			case "xpm":
				$mime = "image/x-xpixmap";
				break;
			case "xwd":
				$mime = "image/x-xwindowdump";
				break;
			case "msh":
				$mime = "model/mesh";
				break;
			case "mesh":
				$mime = "model/mesh";
				break;
			case "silo":
				$mime = "model/mesh";
				break;
			case "wrl":
				$mime = "model/vrml";
				break;
			case "vrml":
				$mime = "model/vrml";
				break;
			case "css":
				$mime = "text/css";
				break;
			case "asc":
				$mime = "text/plain";
				break;
			case "txt":
				$mime = "text/plain";
				break;
			case "gpg":
				$mime = "text/plain";
				break;
			case "rtx":
				$mime = "text/richtext";
				break;
			case "rtf":
				$mime = "text/rtf";
				break;
			case "wml":
				$mime = "text/vnd.wap.wml";
				break;
			case "wmls":
				$mime = "text/vnd.wap.wmlscript";
				break;
			case "etx":
				$mime = "text/x-setext";
				break;
			case "xsl":
				$mime = "text/xml";
				break;
			case "flv":
				$mime = "video/x-flv";
				break;
			case "mpeg":
				$mime = "video/mpeg";
				break;
			case "mpg":
				$mime = "video/mpeg";
				break;
			case "mpe":
				$mime = "video/mpeg";
				break;
			case "qt":
				$mime = "video/quicktime";
				break;
			case "mov":
				$mime = "video/quicktime";
				break;
			case "mxu":
				$mime = "video/vnd.mpegurl";
				break;
			case "avi":
				$mime = "video/x-msvideo";
				break;
			case "movie":
				$mime = "video/x-sgi-movie";
				break;
			case "asf":
				$mime = "video/x-ms-asf";
				break;
			case "asx":
				$mime = "video/x-ms-asf";
				break;
			case "wm":
				$mime = "video/x-ms-wm";
				break;
			case "wmv":
				$mime = "video/x-ms-wmv";
				break;
			case "wvx":
				$mime = "video/x-ms-wvx";
				break;
			case "ice":
				$mime = "x-conference/x-cooltalk";
				break;
			case "rar":
				$mime = "application/x-rar";
				break;
			default:
				$mime = "application/octet-stream";
				break;
		}
		return $mime;
	}


	/**
	 * Get Departments Array which recognize to a staff
	 * Return an Array, String or Object
	 */
	public static function getStaffDepartmentsArray( $staff_id, $type = 'string' ) {
		$db = JFactory::getDBO();

		# get departments of the staff
		$query_get_departments = '
			SELECT `department_id`
			FROM
				`#__obhelpdesk3_staff_department`
			WHERE
				`user_id` = ' . $staff_id . '
		';
		$db->setQuery( $query_get_departments );
		$departments = $db->loadObjectList();
		$de_arr      = array();
		foreach ( $departments AS $department ) {
			$de_arr[] = $department->department_id;
		}

		if ( $type == 'string' ) {
			return implode( ',', $de_arr );
		}
	}

	/**
	 * Load Newest Tickets into an object
	 *
	 * @param unknown_type $department
	 */
	public static function loadNewestTickets( $limit = 10 ) {
		$db               = JFactory::getDbo();
		$staff            = JFactory::getUser();
		$department_array = obHelpDeskHelper::getStaffDepartmentsArray( $staff->id );
		if ( ! $department_array || ! count( $department_array ) ) {
			return array();
		}
		$query = '
			SELECT t.`id`, 
			t.`subject`, 
			t.`created`, 
			t.`priority`, 
			p.`title` as `priority_name`, 
			p.`color` as `priority_color`, 
			d.`label_color` AS `label_color`,
			t.`customer_id`,
			t.`staff`,
			d.`prefix` AS `prefix`,
			CONCAT(d.prefix, "-", t.id) as prefix_code
			FROM
				`#__obhelpdesk3_tickets` AS t,
				`#__obhelpdesk3_departments` AS d,
				`#__obhelpdesk3_priority` AS p
			WHERE
				t.`departmentid` = d.`id` AND 
				t.`status`=\'open\' AND
				d.`id` IN (' . $department_array . ') AND
				t.priority = p.id
			ORDER BY t.`created` DESC
			LIMIT ' . $limit . '
		';
		$db->setQuery( $query );
		$res = $db->loadObjectList();
		if ( $db->getErrorNum() ) {
			echo '<pre>' . print_r( $db->getErrorMsg(), true ) . '</pre>';
			exit( '' . __LINE__ );
		}

		return $res;
	}

	/**
	 * Load Overdue Tickets into an object
	 *
	 * @param unknown_type $department
	 * TODO:
	 * - Can sua lai query cho dung khi du lieu test day dau
	 */
	public static function loadOverdueTickets( $limit = 10, $staffonly = 0 ) {
		$db               = JFactory::getDBO();
		$staff            = JFactory::getUser();
		$department_array = obHelpDeskHelper::getStaffDepartmentsArray( $staff->id );
		if ( ! $department_array || ! count( $department_array ) ) {
			return array();
		}
		$overduetime = obHelpDeskHelper::getConfig( 'overduetime' )->value;

		$query = ' SELECT t.`id`,
			t.`staff` AS `user_id`,
			t.`subject`, 
			t.`created`, 
			t.`priority`, 
			p.`title` AS `priority_name`, 
			p.`color` AS `priority_color`,
			d.`label_color` AS `label_color`,
			t.`customer_id`, 
			t.`staff`,
			d.`prefix` AS `prefix`,
			CONCAT(d.prefix, "-", t.id) as prefix_code
			FROM
				`#__obhelpdesk3_tickets` AS t,
				`#__obhelpdesk3_departments` As d,
				`#__obhelpdesk3_priority` AS p
			WHERE
				t.`departmentid` = d.`id` AND 
				t.`status`=\'open\' AND
				t.priority = p.id AND
		';

		if ( $staffonly ) {
			$query .= '
				t.`staff`=' . $staff->id . ' AND
			';
		}

		$query .= '
				d.`id` IN (' . $department_array . ')
			ORDER BY t.`created`
			LIMIT ' . $limit . '
		';
		$db->setQuery( $query );
		$tickets = $db->loadObjectList();
		if ( $tickets == null ) {
			return null;
		}
		$odtArr = array();
		foreach ( $tickets AS $ticket ) {
			if ( obHelpDeskHelper::isOverdueTicket( $ticket->id ) ) {
				$odtArr[] = $ticket;
			}
		}

		return $odtArr;
	}

	/**
	 * Check if a ticket is overdue or not
	 * return true if the ticket is overdue, otherwise it returns false
	 */
	public static function isOverdueTicket( $ticket_id ) {
		$option    = 'com_obhelpdesk';
		$mainframe = JFactory::getApplication( 'site' );
		# get overduetime
		$overduetime = obHelpDeskHelper::getConfig( 'overduetime' )->value;
		$db          = JFactory::getDBO();

		# get last message time
		$query = '
			SELECT max(m.`reply_time`) as last_message, m.`user_id`, m.`tid`
			FROM 
				`#__obhelpdesk3_messages` AS m
			INNER JOIN
				`#__obhelpdesk3_tickets` AS t
			ON 
				m.`tid` = t.`id` AND
				m.`tid` = ' . $ticket_id . ' AND
				t.`status` 	= \'open\' 
			GROUP BY 
				m.`tid`
		';
		$db->setQuery( $query );
		$result = $db->loadObject();
		if ( ! $result || ! $result->last_message ) {
			return false;
		} else {
			$current_time = time();
			$timezone     = date( 'Z', time() );
			$reply_time   = strtotime( $result->last_message );
			if ( ( $current_time - $reply_time - $timezone ) >= $overduetime ) { // pass overduetime => return true
				return true;
			} else { // return false
				return false;
			}
		}
	}

	public static function getKeywords( $subject, $pattern = '/\{[^\}]+\}/i' ) {
		preg_match_all( $pattern, $subject, $result );
		if ( $result ) {
			return $result[0];
		}

		return array();
	}

	public static function getStaffList( $str ) {
		$staffs = array();
		if ( $str ) {
			$uids = explode( ',', $str );
			foreach ( $uids as $uid ) {
				$staff    = JFactory::getUser( $uid );
				$staffs[] = $staff;
			}
		}

		return $staffs;
	}

	public static function getStaffIdsInDepartment( $did ) {
		$db  = JFactory::getDbo();
		$sql = "SELECT 
					`user_id`
				FROM
					`#__obhelpdesk3_staff_department`
				WHERE
					`department_id` = {$did}";
		$db->setQuery( $sql );
		$staff_ids = $db->loadColumn();

		return $staff_ids;
	}

	/**
	 * Return id of free staff
	 *
	 * @param number $did
	 *
	 * @return unknown
	 */
	public static function getFreeStaff( $did = 0 ) {
		$db  = JFactory::getDbo();
		$sql = "SELECT 
					`staff`, count(*) as `count`
				FROM
					`#__obhelpdesk3_tickets`
				WHERE
					`status` = 'open' AND `departmentid` = {$did}
						AND `staff` > 0
				GROUP BY `staff`
				ORDER BY `count` ASC
				LIMIT 1";
		$db->setQuery( $sql );
		$res = $db->loadObject();
		if ( $res ) {
			return $res->staff;
		} else {
			$sql = "SELECT 
						`user_id`
					FROM
						`#__obhelpdesk3_staff_department`
					WHERE
						`department_id` = {$did}
					LIMIT 1";
			$db->setQuery( $sql );
			$res = $db->loadResult();

			return $res;
		}
	}

	/**
	 * load Announcements from obHelpDesk Settings
	 */
	public static function loadAnnouncements( $page = 'all' ) {
		$componentParams        = JComponentHelper::getParams( 'com_obhelpdesk' );
		$announcement_general   = $componentParams->get( 'announcement_general' );
		$announcement_tickets   = $componentParams->get( 'announcement_tickets' );
		$announcement_newticket = $componentParams->get( 'announcement_newticket' );
		$announcement           = "
			<div class='obhelpdesk_announcement obhelpdesk_announcement_general'>
				{$announcement_general}
			</div>
		";
		if ( $page == 'tickets' ) {
			$announcement .= "
				<div class='obhelpdesk_announcement obhelpdesk_announcement_tickets'>
					{$announcement_tickets}
				</div>
			";
		}
		if ( $page == 'newticket' ) {
			$announcement .= "
				<div class='obhelpdesk_announcement obhelpdesk_announcement_newticket'>
					{$announcement_newticket}
				</div>
			";
		}

		return $announcement;
	}
}