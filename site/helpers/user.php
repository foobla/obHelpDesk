<?php 
defined('JPATH_PLATFORM') or die;

class obHelpDeskUserHelper {
	public static $users = array();
	public static $emails = array();
	/**
	 * Check permission of user.
	 * Input: $permission, $userid
	 * Output: Boolean value.
	 */
	public static function checkPermission($userid, $permission) {
		$app = JFactory::getApplication();
		if($userid){
			$db = JFactory::getDbo();
			$query = "SELECT g.* FROM `#__obhelpdesk3_staffs` a " 
					." LEFT JOIN  `#__obhelpdesk3_groups` g ON a.group_id = g.id"
					." WHERE `user_id` = $userid";
			$db->setQuery($query);
			$row = $db->loadObject();
			if($row) if($row->$permission) return true;
		}else{
			
			$obhelpdesk_logged = $app->getUserState('obhelpdesk_logged');
			if( $obhelpdesk_logged ){
				$email 	= $app->getUserState('obhelpdesk_ticket_email');
				$code 	= $app->getUserState('obhelpdesk_ticket_code');
				$tid 	= JRequest::getVar('id');
				$query 	= 'SELECT
								COUNT(*)
							FROM
								`#__obhelpdesk3_tickets`
							WHERE
								`id`='.$tid.' 
								AND `customer_email` = "'. addslashes( $email ) .'" 
								AND `quickcode`="'. addslashes( $code ) .'"';
				$db = JFactory::getDbo();
				$db->setQuery($query);
				$res = $db->loadResult();
				if($res){
					return true;
				} else {
					return false;
				}
			}
			
		}
		return false;
	}
	/**
	 * Check user is a staff or not.
	 */
	public static function is_staff($userid) {
		$db = JFactory::getDbo();
		$query = "SELECT count(*) FROM `#__obhelpdesk3_staffs` WHERE `user_id` = $userid";
		$db->setQuery($query);
		if($db->loadResult()) return true;
		return false;
	}
	
	/**
	 * Check user have permission submit ticket on department.
	 */
	public static function checkDepartmentPermission($groups, $userid){
		$user = JFactory::getUser($userid);
		// Check permission create ticket in this Deparment
		$ugroups = array();
		if($groups) $ugroups = explode(',', $groups);
		
		//IF Public
		if(in_array(1, $ugroups)) return true;
		
		if($user->id) {
			foreach($user->groups as $v){
				// If current user have one group in array usergroups.
				if(in_array($v, $ugroups)) return true; 
			}
		}
		return false;
	}
	
	/**
	 * Check Valid extension of file upload.
	 */
	public static function checkFilesUpload($files, $allowExts) {
		$arr_allowExts = ($allowExts) ? explode(',', $allowExts) : array();
		
		if($files) {
			foreach ($files as $file) {
				$ext = pathinfo($file, PATHINFO_EXTENSION);
				if(!in_array($ext, $arr_allowExts)) return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Check permission upload file
	 * 
	 */
	public static function checkPermissionUpload($permission, $logged) {
		switch ($permission) {
			case 'yes':
				return true;
			case 'no':
				break;
			case 'users':
				if($logged) return true;
			default:
				break;
		}
		
		return false;
	}
	
	/**
	 * Auto generate password to logged in list tickets page.
	 */
	public static function generatePassword($length = 8) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$count = mb_strlen($chars);

		for ($i = 0, $result = ''; $i < $length; $i++) {
			$index = rand(0, $count - 1);
			$result .= mb_substr($chars, $index, 1);
		}

		return $result;
	}

	public static function CheckPermissionViewListTicket($userid) {
		$session = JFactory::getSession();
		if($userid) {
			return true;
		} elseif($session->get('obhelpdesk_logged')) {
			return true;
		}
		return false;
	}
	
	public static function getStaffDepartment($userid) {
		$db = JFactory::getDbo();
		$query = "SELECT `department_id` FROM `#__obhelpdesk3_staff_department` WHERE user_id=".$userid;
		$db->setQuery($query);
		return $db->loadColumn();
	}
	
	public static function checkViewTicketPermission($userid, $tid, $is_staff = false) {
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$obhelpdesk_logged = $app->getUserState('obhelpdesk_logged');
		if(!$is_staff && ($userid or $obhelpdesk_logged)){
			$query = '';
			if($userid) {
				$user_tmp = JFactory::getUser($userid);
				$email = $user_tmp->email;
				$query = "SELECT
							COUNT(*)
						FROM
							`#__obhelpdesk3_tickets`
						WHERE
							customer_email = '".$email."'
							AND id=".$tid;
			} else {
				#$email = $session->get('obhelpdesk_email');
				$email 	= $app->getUserState('obhelpdesk_ticket_email');
				$code 	= $app->getUserState('obhelpdesk_ticket_code');
				$query 	= 'SELECT
								COUNT(*)
							FROM
								`#__obhelpdesk3_tickets`
							WHERE
								`id`='.$tid.' 
								AND `customer_email` = "'. addslashes( $email ) .'" 
								AND `quickcode`="'. addslashes( $code ) .'"';
			}
			
			$res = $db->setQuery($query);
			return $db->loadResult();
			
		} elseif( $is_staff ) {
			JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');
			
			// load ticket
			$ticket = JTable::getInstance('Ticket', 'obHelpDeskTable');
			$ticket->load($tid);
			$did = $ticket->departmentid; // Department ID
			
			$query = "SELECT COUNT(*) FROM `#__obhelpdesk3_staff_department` WHERE department_id =".$did." AND user_id=".$userid;
			return $db->loadResult();
		}
		
		return false;
	}
	
	
	/**
	 * Check Reply Ticket permission
	 * @param unknown $userid
	 * @return boolean
	 */
	public static function checkReplyTicketPermission($userid=null, $ticket_id=0) {
		// check permission with staff
		$app 		= JFactory::getApplication();
		$session 	= JFactory::getSession();
		$is_staff 	= obHelpDeskUserHelper::is_staff($userid);
		if($is_staff) {
			if(!self::checkPermission($userid, 'answer_ticket')) {
				return false;
			}
		} elseif(!$userid) {
			$obhelpdesk_logged = $app->getUserState('obhelpdesk_logged');
			if( !$obhelpdesk_logged ) {
				return false;
			} else {
				if( !$ticket_id ){
					$ticket_id = JRequest::getVar('id');
					if(!$ticket_id) return false;
				}
				$email 	= $app->getUserState('obhelpdesk_ticket_email');
				$code 	= $app->getUserState('obhelpdesk_ticket_code');
				$query 	= 'SELECT
								COUNT(*)
							FROM
								`#__obhelpdesk3_tickets`
							WHERE
								`id`='.$ticket_id.' 
								AND `customer_email` = "'. addslashes( $email ) .'" 
								AND `quickcode`="'. addslashes( $code ) .'"';
				$db = JFactory::getDbo();
				$db->setQuery($query);
				$res = $db->loadResult();
				if($db->getErrorNum()){
					return false;
				}
				if( $res ) {
					return true;
				} else {
					return false;
				} 
			}
		}
		return true;
	}

	public static function NewUserProcess( $email, $fullname, $password ) {
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();

		$query = "SELECT count(*) FROM `#__obhelpdesk3_customers` WHERE `email` = '".$email."'";
		$db->setQuery($query);
		$result1 = $db->loadResult();
		
		$query = $db->getQuery(true);
		$query = "SELECT count(*) FROM `#__users` WHERE `email` = '".$email."' AND `block` = 0";
		$db->setQuery($query);
		$result2 = $db->loadResult();
		
		if(!$result1 && !$result2) {
			$password = md5($password);
			$query = "INSERT INTO `#__obhelpdesk3_customers` SET `email` = '".$email."', `fullname`='".$fullname."', `password`='".$password."'";
			$db->setQuery($query);
			$db->query();
			return true;
		}

		return false;
	}
	
	public static function getUserInfoByEmail($email){
		if(!array_key_exists($email, self::$emails)){
			$db = JFactory::getDbo();
			$query = "SELECT `id` FROM `#__users` WHERE `email`='".$email."' AND `block`=0";
			$db->setQuery($query);
			$userid = $db->loadResult();
			if($userid){
				self::$emails[$email]=$userid;
			}else{
				return null;
			}
		}
		return JFactory::getUser(self::$emails[$email]);
	}
	
	public static function getNameByEmailAndID( $email, $uid=0 ) {
		if( $uid ) {
			$user_from_uid = JFactory::getUser($uid);
			if($user_from_uid) {
				$email = $user_from_uid->email;
			}
		}
		$db = JFactory::getDbo();
		$query = "SELECT `name` FROM `#__users` WHERE `email`='".$email."'";
		$db->setQuery($query);
		$name = $db->loadResult();
		if( $name ) {
			$fname = strstr($name, ' ', true);
			$fname = (trim($fname))?$fname:$name;
			return array("fullname"=>$name, "firstname"=>$fname);
		}
		$qry = "SELECT `fullname` FROM `#__obhelpdesk3_customers` WHERE `email`= '".$email."'";
		$db->setQuery($qry);
		$name = $db->loadResult();
		$fname = strstr($name, ' ', true);
		$fname = (trim($fname))?$fname:$name;
		return array("fullname"=>$name, "firstname"=>$fname);
	}
	
	public static function getStaffList($default = null, $disabled = false){
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		
		// get list departments
		$query = "SELECT `department_id` FROM `#__obhelpdesk3_staff_department` WHERE `user_id` = ".$user->id;
		$db->setQuery($query);
		$dids = $db->loadColumn();
		
		if(count($dids)) {
			$query = "SELECT a.`user_id` FROM `#__obhelpdesk3_staffs` as a"
					." LEFT JOIN `#__obhelpdesk3_staff_department` as b ON a.`user_id` = b.`user_id`"
					." WHERE b.`department_id` IN (".implode(',', $dids).")";
			$db->setQuery($query);
			$user_ids = $db->loadColumn();
			if(!count($user_ids)) return false;
			$query = "SELECT `name` as text, `id` as value FROM `#__users` WHERE `id` IN (".implode(',', $user_ids).") AND `block` = 0";
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			
			$arr_obj[] = JHTML::_('select.option', '_none', JText::_('SELECT_STAFF'));
			$arr_obj[] = JHTML::_('select.option', '0', JText::_('OBHELPDESK_UNASSIGNED'));
			if(count($rows)){
				foreach ($rows as $row){
					$obj = new stdClass();
					$obj->text = $row->text;
					$obj->value = $row->value;
					array_push($arr_obj, $obj);
				}
			}
			
			$str_disabled = '';
			if($disabled) $str_disabled = ' disabled="true"';
			
			$javascript = ' onchange="if(document.adminForm.operator.value > 0) document.adminForm.operator.value++; else document.adminForm.operator.value--;"';
			return JHTML::_('select.genericlist',  $arr_obj, 'staff_id', 'class="inputbox"'.$str_disabled.$javascript, 'value', 'text', $default);
		} else {
			return false;
		}
	}
	
	public static function getProfileAvatar($user_id, $size = 32)
	{
		$user 	= JFactory::getUser($user_id);
		$db		= JFactory::getDBO();
		
		# get Avatar handler
		$obj = obHelpDeskHelper::getConfig('avatar');
		$avatar_handle = $obj->value;
		if ($avatar_handle == 'com_comprofiler') { # Community Builder: done 
			$query = '
				SELECT `avatar`
				FROM
					`#__comprofiler`
				WHERE
					`user_id` = '.$user->id.'
				LIMIT 1
			';
			$db->setQuery($query);
			$avatar = $db->loadResult();
			
			if($avatar==NULL) {
				# ignore CB template stuff
				$avatar = JURI::base().'components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png';
			} else {
				$avatar = JURI::base().'images/comprofiler/'.$avatar;
			}
		} elseif ($avatar_handle == 'gravatar') { # Gravatar: done
			$hash		= md5( strtolower( trim( $user->email ) ) );
			$avatar 	= 'http://www.gravatar.com/avatar/'.$hash.'?s='.$size;
		} elseif ($avatar_handle == 'com_kunena') { # Kunena: done
			$query = '
				SELECT `avatar`
				FROM
					`#__kunena_users`
				WHERE
					`user_id` = '.$user->id.'
				LIMIT 1
			';
			$db->setQuery($query);
			$avatar = $db->loadResult();
			
			if($avatar==NULL) {
				$avatar = 's_nophoto.jpg';
			}			
			$avatar = JURI::base().'media/kunena/avatars/'.$avatar;
		} elseif ($avatar_handle == 'com_community') { # JomSocial: done
			$query = '
				SELECT `avatar`
				FROM
					`#__community_users`
				WHERE
					`userid` = '.$user->id.'
				LIMIT 1
			';
			$db->setQuery($query);
			$avatar = $db->loadResult();
			
			if($avatar==NULL) {
				$avatar = 'components/com_community/assets/default.jpg';
			}
			$avatar = JURI::base().$avatar;
		} elseif ($avatar_handle == 'com_alphauserpoints') { # AlphaUserPoints: done
			$query = '
				SELECT `avatar`
				FROM
					`#__alpha_userpoints`
				WHERE
					`userid` = '.$user->id.'
				LIMIT 1
			';
			$db->setQuery($query);
			$avatar = $db->loadResult();
			
			if($avatar==NULL) {
				$avatar = 'generic_gravatar_grey.png';
			}
			
			$avatar = JURI::base().'components/com_alphauserpoints/assets/images/avatars/'.$avatar;
		} else { # none
			$avatar = '';
		}
		return 	$avatar;
	}

	public static function getProfileLink($user_id)
	{
		$option = 'com_obhelpdesk';
		$mainframe = JFactory::getApplication('site');
		$user 	= JFactory::getUser($user_id);
		$db		= JFactory::getDBO();
		
		# get Profile handler
		$profile_handles 	= obHelpDeskHelper::getConfig('userprofiler');
		$profile_handle		= $profile_handles->value;
		$profile_customurl	= obHelpDeskHelper::getConfig('userprofiler_custom_url');
		$profile_customurl	= $profile_customurl->value;
		
		# Get Itemid of the Profile handler
		$query 	= '
			SELECT `id`
			FROM
				`#__menu`
			WHERE
				`link` LIKE \'%'.$option.'%\' AND
				`published` = 1
		';
		$db->setQuery($query);
		$profileItemid = $db->loadResult();

		if ($profile_handle == 'com_comprofiler') {
			$link = 'index.php?option=com_comprofiler&task=userprofile&user='.$user_id.'&Itemid='.$profileItemid;
		} elseif ($profile_handle == 'com_kunena') {
			$link = 'index.php?option=com_kunena&func=profile&userid='.$user_id.'&Itemid='.$profileItemid;
		} elseif ($profile_handle == 'com_community') {
			$link = 'index.php?option=com_community&view=profile&userid='.$user_id.'&Itemid='.$profileItemid;
		} elseif ($profile_handle == 'com_alphauserpoints') {
			$query = '
				SELECT `referreid`
				FROM
					`#__alpha_userpoints`
				WHERE
					`userid` = '.$user_id.'
				LIMIT 1
			';
			$db->setQuery($query);
			$alpha_userid = $db->loadResult();
			
			$link = 'index.php?option=com_alphauserpoints&view=account&userid='.$alpha_userid.'&Itemid='.$profileItemid;
		} elseif ($profile_handle == 'custom') {
			$link = str_replace('{id}', $user_id, $profile_customurl);
		} else { // the same with com_virtuemart
			return NULL;
		}
		
		return JURI::base().$link;
	}

	public static function getProfileHolder( $item, $extras = true, $customer = true ) {
		$email = '';
		if($customer){
			$email = $item->customer_email;
		}else {
			$staff = JFactory::getUser($item->staff);
			$email = $staff->email;
		}
		if( !isset(self::$users[$email])) {
			$user 		= JFactory::getUser();
			$is_staff 	= obHelpDeskUserHelper::is_staff($user->id);
			
			$reply_name = '';
			if($customer ){
				$reply_name 	= obHelpDeskUserHelper::getNameByEmailAndID($item->customer_email, $item->customer_id);
			} else {
				$reply_name 	= obHelpDeskUserHelper::getNameByEmailAndID($item->email_reply, $item->uid_reply);
			}

// 			var_dump($reply_name);
			
			$profile_icons = '';

			if ( $extras && $is_staff ) :
				JPluginHelper::importPlugin('obhelpdesk');
				$dispatcher = JDispatcher::getInstance();
				$results = $dispatcher->trigger('onLoadProfile', array($item));
				foreach ($results AS $result) {
					$profile_icons .= $result;
				}
			endif;

			if ($customer) {
				$profile_link = obHelpDeskUserHelper::getProfileLink($item->customer_id);
			} else {
				$profile_link = obHelpDeskUserHelper::getProfileLink($item->staff);
			}
	
			$profile_holder	= '
				<a href="'.$profile_link.'" class="hasTip" title="'.$reply_name['fullname'].'">'.$reply_name['firstname'].'</a>
			';

			if ($extras) {
				$profile_holder	= $profile_icons.$profile_holder;
			}
			self::$users[$email]=array('holder'=>$profile_holder, 'name'=>$reply_name['firstname']);
			if ( !$is_staff ) {
				return $reply_name['firstname'];
			}
			return self::$users[$email]['holder'];
		}
		return self::$users[$email]['holder'];
	}

	// Get open/all tickets from a customer
	function getTicketsStats($user_id) {
		$option 	= 'com_obhelpdesk';
		$mainframe	= JFactory::getApplication('site');
		$user		= JFactory::getUser($user_id);
		$db			= JFactory::getDBO();

		// All tickets
		$query = '
			SELECT
				COUNT(*)
			FROM
				`#__obhelpdesk3_tickets`
			WHERE
				`customer_id` = '.$user_id.'
		';
		$db->setQuery($query);
		$all = $db->loadResult();

		// Open tickets
		$query = '
			SELECT
				COUNT(*)
			FROM
				`#__obhelpdesk3_tickets`
			WHERE
				`customer_id` = '.$user_id.' AND
				`status` = "open"
		';
		$db->setQuery($query);
		$open = $db->loadResult();
		return array($all, $open);
	}

	public static function createUser( $name, $email ) {
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$sql = "SELECT * FROM `#__users` WHERE `email`='{$email}'";
		$db->setQuery($sql);
		$res = $db->loadObject();
		if( $res ){
			$newuser = new JUser();
			$newuser->load( $res->id );
			return $newuser;
		}
		list($username,$t) = explode( '@', $email );
		$username = preg_replace('/[^A-Z0-9_\.-]/i', '', $username);
		$username_t = $username;
		$i = 0;
		$exits = true;
		do{
			$sql = "SELECT * FROM `#__users` WHERE `username`='{$username}'";
			$db->setQuery($sql);
			$res = $db->loadObject();

			if( $res ){
				$exits = true;
				$i++;
				$username = $username_t.$i;
			} else {
				$exits = false;
			}
		} while( $exits );

		$userdata = array(
				'username'=>$username,
				'email'=>$email,
				'name'=>$name ,
				'groups'=>array(2)
					
		);
		$userdata['activation'] = JApplication::getHash(JUserHelper::genRandomPassword());
		$userdata['block'] = 0;
		jimport('joomla.user.user');
		$newuser = new JUser();
		$newuser->username = $username;
		$newuser->name = $name;
		$newuser->bind( $userdata );
		$newuser->save();

		$config = JFactory::getConfig();

		$userdata = $newuser->getProperties();
		$userdata['fromname']	= $config->get('fromname');
		$userdata['mailfrom']	= $config->get('mailfrom');
		$userdata['sitename']	= $config->get('sitename');
		$userdata['siteurl']	= JUri::root();
		$lang 	= JFactory::getLanguage();
		$lang->load('com_users');

		$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$userdata['name'],
				$userdata['sitename']
		);

		$emailBody = JText::sprintf (
				'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY',
				$userdata['name'],
				$userdata['sitename'],
				$userdata['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$userdata['activation'],
				$userdata['siteurl'],
				$userdata['username'],
				$userdata['password_clear']
		);

		// Send the registration email.
// 		$return = JFactory::getMailer()->sendMail($userdata['mailfrom'], $userdata['fromname'], $userdata['email'], $emailSubject, $emailBody);
		return $newuser;
	}
	
// 	public static function getUserName( $user_id ){
// 		if(self::$users && isset(self::$users[$user_id])) {
// 			return self::$users[$user_id]->name;
// 		} else {
// 			$user = JFactory::getUser( $user_id );
// 			if($user->id){
// 				self::$users[$user->id] = $user;
// 				return $user->name;
// 			}
// 			return '';
// 		}
// 	}
	
// 	public static function getProfile( $user_id ) {
// 		$user = null;
// 		if(self::$users && isset(self::$users[$user_id])) {
// 			$user = self::$users[$user_id];
// 		} else {
// 			$user = JFactory::getUser( $user_id );
// 			if($user->id){
// 				self::$users[$user->id] = $user;
// 			}
// 		}
// 		if(!$user) return null;
// // 		return $a
// 	}
}