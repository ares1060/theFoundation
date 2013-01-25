<?php
	class UserDataHelper extends TFCoreFunctions{
		protected $name = 'User';
		
		private $users;
		private $groups;
		
		private $userDataForGroup; // stores user Data by Group (cache)
		
		function __construct($settings){
			parent::__construct();
			$this->settings = $settings;
			$this->users = array();
			$this->groups = array();
		}	
		
		/** ---  Getter --- */
		/**
		 * returnes nick availability
		 * @param unknown_type $nick
		 */
		public function checkNickAvailability($nick){
			$u = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE nick="'.mysql_real_escape_string($nick).'"');
			if($u != array()) return false;
			else return true;
		}
		/* ========== USERS ========= */
		/**
		 * returnes Users 
		 * @param unknown_type $page
		 * @param unknown_type $perPage
		 */
		public function getUsers($page=-1, $perPage=-1) {
			$return = array();
        	
			$all = $this->getAllUserCount(-1, -1);
        	
			$from = ($page-1)*($this->_setting('perpage.user'));
			if($from > $all) $from = 0;
			
			$limit = ($page == -1) ? '' : 'LIMIT '.mysql_real_escape_string($from).', '.mysql_real_escape_string($this->_setting('perpage.user')).';';
			
			$u1 = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user '.$limit);
			if($u1 != array()){
				foreach($u1 as $u) 
					$return[] = new UserObject($u['nick'], $u['id'], $u['email'], $this->getUserGroup($u['group']), $u['status']);
			}
			return $return;
		}
		
		public function getUserById($id){
			return $this->getUser($id);
		}
		
		/**
		 * returnes count of all users
		 */
		public function getAllUserCount(){
			$u = $this->mysqlRow('SELECT COUNT(*) count FROM '.$GLOBALS['db']['db_prefix'].'user');
			if($u) return $u['count'];
			else return -1;
		}
		/**
		 * returnes User by Id
		 * @param unknown_type $id
		 */
		public function getUser($id){
			if(!isset($this->users[$id])) {
				$u = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE id="'.mysql_real_escape_string($id).'"');
				if($u != array()){
					$this->users[$id] = new UserObject($u['nick'], $u['id'], $u['email'], $this->getUserGroup($u['group']), $u['status']);
				}
			}
			return $this->users[$id];
		}
		
		/**
		 * returnes User by Nick
		 * @param unknown_type $nick
		 */
		public function getUserByNick($nick){
			foreach($this->users as $u){
				if($u->getNick() == $nick) return $nick;
			}
			$u = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE nick="'.mysql_real_escape_string($nick).'"');
			if($u != array()){
				$this->users[$u['id']] = new UserObject($u['nick'], $u['id'], $u['email'], $this->getUserGroup($u['group']), $u['status']);
				return $this->users[$u['id']];
			}
			return null;
		}
		
		/**
		 * gets User Info Object by User Data id and data value
		 * @param unknown_type $data_id
		 * @param unknown_type $value
		 */
		public function getUserByData($data_id, $value){
			if($data_id > 0 && $value != ''){
				$array = $this->mysqlArray('SELECT * FROM
						'.$GLOBALS['db']['db_prefix'].'userdata_user du
						LEFT JOIN '.$GLOBALS['db']['db_prefix'].'user u ON du.u_id = u.id
						WHERE du.value=\''.mysql_real_escape_string($value).'\' AND du.ud_id = \''.mysql_real_escape_string($data_id).'\';');
				 
				if($array != array()) {
					$u = $array[0];
					$this->users[$u['id']] = new UserObject($u['nick'], $u['id'], $u['email'], $this->getUserGroup($u['group']), $u['status']);
		
					return $this->getUser($array[0]['id']);
				}
				else return null;
			} else return null;
		}
		/**
		 * returnes User by EMail
		 * @param unknown_type $mail
		 */
		public function getUserByEMail($mail){
			foreach($this->users as $u){
				if($u->getEMail() == $nick) return $nick;
			}
			$u = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE email="'.mysql_real_escape_string($mail).'"');
			if($u != array()){
				$this->users[$u['id']] = new UserObject($u['nick'], $u['id'], $u['email'], $this->getUserGroup($u['group']), $u['status']);
				return $this->users[$u['id']];
			}
			return null;
		}
		/**
		 * returnes users Hash for Login routine
		 * @param unknown_type $mail
		 */
		public function getUserHashByEMail($mail){
			$u = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE email="'.mysql_real_escape_string($mail).'"');
	       	if($u != '' && $u != array() && isset($u['hash'])){
	       		return $u['hash'];
	       	} else return '';
		}
		
		/**
		 * returnes users Hash for Login routine
		 * @param unknown_type $nick
		 */
		public function getUserHashByNick($nick){
			$u = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE nick="'.mysql_real_escape_string($nick).'"');
	       	if($u != '' && $u != array() && isset($u['hash'])){
	       		return $u['hash'];
	       	} else return '';
		}
		
		/* ========== GROUPS ========= */
		/**
		 * returnes userGroup by Id
		 * @param unknown_type $id
		 */
		public function getUserGroup($id) {
			if(!isset($this->groups[$id])){
				$u = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'usergroup WHERE id="'.mysql_real_escape_string($id).'"');
				if($u != array()){
					$this->groups[$id] = new UserGroup($u['id'], $u['name']);
				}
			}
			return $this->groups[$id];
		}
		/**
		 * returnes array of all Usergroups
		 */
		public function getGroups() {
			$this->groups = array();
			$g = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'usergroup');
			if($g != array()){
				foreach($g as $group) {
					$this->groups[] = new UserGroup($group['id'], $group['name']);
				}
			}
			return $this->groups;
		}
		/* ========== USERDATA GROUP ========= */
		public function getUserDataGroupById($id){
		$q = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'userdata_datagroup WHERE id = "'.mysql_real_escape_string($id).'"');
			if($q != null){
				return new UserDataGroup($q['id'], $q['name']);
			}
		}
		/* ========== USERDATA ========= */
		public function getUserDataById($id){
			$q = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'userdata WHERE id = "'.mysql_real_escape_string($id).'"');
			if($q != null){
				$t = new UserData($q['id'], $q['name'], $this->getUserDataGroupById($q['group']), $q['type'], $q['type'], $q['vis_reg'], $q['vis_login'], $q['vis_edit']);
				$q = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'userdata_usergroup WHERE ud_id = "'.mysql_real_escape_string($id).'"');
				foreach($q as $row){
					$t->addUserGroup($row['ug_id']);
				}
				return $t;
			}
			
		}
		
		public function getUserDataByName($name) {
			$q = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'userdata WHERE name = "'.mysql_real_escape_string($name).'"');
			if($q != null){
				$t = new UserData($q['id'], $q['name'], $this->getUserDataGroupById($q['group']), $q['type'], $q['type'], $q['vis_reg'], $q['vis_login'], $q['vis_edit']);
				$q = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'userdata_usergroup WHERE ud_id = "'.mysql_real_escape_string($q['id']).'"');
				foreach($q as $row){
					$t->addUserGroup($row['ug_id']);
				}
				return $t;
			}
		}
		
		/**
		 * returnes UserData object by given id
		 * is used by UserInfo->loadData(ServiceProvider $sp)
		 * @param $id
		 */
		public function getUserDataByUserId($id){
			$user = $this->getUser($id);
			
// 			$data = $this->mysqlArray('SELECT *, udg.name as gName, ud.name as dName FROM `'.$GLOBALS['db']['db_prefix'].'userdata` ud
// 					LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'userdata_usergroup` udg ON ud.g_id = udg.g_id
// 					LEFT JOIN 
// 						(SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'userdata_user` WHERE 
// 								u_id="'.mysql_real_escape_string($user->getId()).'") uud ON uud.d_id = ud.ud_id
// 					LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'userdata_datagroup` ud_g ON ud.ud_id = ud_g.d_id
// 					WHERE ud_g.g_id = "'.mysql_real_escape_string($user->getGroupId()).'"');

			$data = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'userdata_user` ud_u
												LEFT JOIN '.$GLOBALS['db']['db_prefix'].'userdata ud ON ud_u.ud_id = ud.id
												LEFT JOIN (
													SELECT * FROM '.$GLOBALS['db']['db_prefix'].'userdata_usergroup WHERE 
													ug_id = \''.mysql_real_escape_string($user->getGroupId()).'\'
													) ud_ug ON ud_ug.ud_id = ud.id
										WHERE ud_u.u_id = \''.mysql_real_escape_string($user->getId()).'\' AND
											   ud_ug.ug_id = \''.mysql_real_escape_string($user->getGroupId()).'\'');
			
			$data_ar = array();
			if($data != array()) {
				foreach($data as $d){
					$data_ar[$d['name']] = $d['value'];
				}
			}
			return $data_ar;
		}
		
		public function setUserDataByUserIdAndDataName($id, $name, $value){
			$user = $this->getUser($id);
			$data = $this->getUserDataByName($name);
// 			echo 'UPDATE `'.$GLOBALS['db']['db_prefix'].'userdata_user` ud_u 
// 													LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'userdata` ud ON ud_u.ud_id = ud.id
// 												SET ud_u.value ="'.mysql_real_escape_string($value).'"
// 												WHERE ud.name ="'.mysql_real_escape_string($name).'" AND ud_u.u_id="'.mysql_real_escape_string($user->getId()).'"';
			$query = $this->mysqlRow('SELECT COUNT(*) c FROM `'.$GLOBALS['db']['db_prefix'].'userdata_user` WHERE ud_id ="'.mysql_real_escape_string($data->getId()).'" AND u_id="'.mysql_real_escape_string($user->getId()).'"');
			
			if($query) {
				if($query['c'] > 0) {
					$query = $this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'userdata_user` SET value ="'.mysql_real_escape_string($value).'"
												WHERE ud_id ="'.mysql_real_escape_string($data->getId()).'" AND u_id="'.mysql_real_escape_string($user->getId()).'"');
				} else {
					$query = $this->mysqlUpdate('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_user` (`value`, `ud_id`, `u_id`) values
												("'.mysql_real_escape_string($value).'", "'.mysql_real_escape_string($data->getId()).'", "'.mysql_real_escape_string($user->getId()).'")');
												
				}
				
				return $query;
			} else return false;
		}
		
		public function getUserData($page=-1, $perPage=-1){
			$return = array();
        	
			$all = $this->getAllUserDataCount(-1, -1);

			$from = ($page-1)*($this->_setting('perpage.user_data'));
			if($from > $all) $from = 0;
			
			$limit = ($page == -1) ? '' : 'LIMIT '.mysql_real_escape_string($from).', '.mysql_real_escape_string($this->_setting('perpage.user_data')).';';
			
			$u1 = $this->mysqlArray('SELECT ud.id d_id, ud.name d_name, ud.group d_group,
											udg.name d_group_name, ud.type d_type,
											ud.info d_info, ud.vis_reg d_vis_reg,
											ud.vis_login d_vis_login, ud.vis_edit d_vis_edit FROM '.$GLOBALS['db']['db_prefix'].'userdata ud 
											LEFT JOIN '.$GLOBALS['db']['db_prefix'].'userdata_datagroup udg ON ud.group = udg.id ORDER BY ud.group '.$limit.'');
			if($u1 != array()){
				foreach($u1 as $u) {
					$tmp = new UserData($u['d_id'], $u['d_name'], new UserDataGroup($u['d_group'], $u['d_group_name']), $u['d_type'], $u['d_info'], $u['d_vis_reg'], $u['d_vis_login'], $u['d_vis_edit']);
					$u2 = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'userdata_usergroup WHERE ud_id="'.mysql_real_escape_string($u['d_id']).'"');
					if($u2 != array()){
						foreach($u2 as $u3){
							$tmp->addUserGroup($u3['ug_id']);
						}
					}
					$return[] = $tmp;
					unset($tmp);
				}
			}
			return $return;
		}
		/**
		 * returnes count of all userdata
		 */
		public function getAllUserDataCount(){
			$u = $this->mysqlRow('SELECT COUNT(*) count FROM '.$GLOBALS['db']['db_prefix'].'userdata');
			if($u) return $u['count'];
			else return -1;
		}
		
		/**
		 * returnes User data for given group
		 * @param unknown_type $group
		 */
		public function getUserDataForGroup($group){
			if(!is_object($group) || get_class($group) != 'UserGroup'){
				$group = $this->getUserGroup($group);
			}
			
			
			if($group != null){
				if(!isset($this->userDataForGroup[$group->getId()])){
					$u1 = $this->mysqlArray('SELECT ud.id d_id, ud.name d_name, ud.group d_group,
											udg.name d_group_name, ud.type d_type,
											ud.info d_info, ud.vis_reg d_vis_reg,
											ud.vis_login d_vis_login, ud.vis_edit d_vis_edit FROM '.$GLOBALS['db']['db_prefix'].'userdata ud 
											LEFT JOIN '.$GLOBALS['db']['db_prefix'].'userdata_datagroup udg ON ud.group = udg.id 
											RIGHT JOIN '.$GLOBALS['db']['db_prefix'].'userdata_usergroup udug ON ud.id = udug.ud_id WHERE udug.ug_id="'.mysql_real_escape_string($group->getId()).'"');
					if($u1 != array()){
						foreach($u1 as $u) {
							if(!isset($this->userDataForGroup[$group->getId()])) $this->userDataForGroup[$group->getId()] = array(); 
							$this->userDataForGroup[$group->getId()][] = new UserData($u['d_id'], $u['d_name'], new UserDataGroup($u['d_group'], $u['d_group_name']), $u['d_type'], $u['d_info'], $u['d_vis_reg'], $u['d_vis_login'], $u['d_vis_edit']);
						}
					}
				}
				return $this->userDataForGroup[$group->getId()];
			} else return false;
			
		}
		
		/** ---  SETTER --- */
		/**
		 * registers user
		 * @param string $nick
		 * @param string $pwd
		 * @param string $email
		 * @param int $group
		 * @param int $status
		 */
    	public function register($nick, $email, $group, $pwd, $pwd2, $status=User::STATUS_HAS_TO_ACTIVATE, $data=array()){
		    if($status == -1) $status = User::STATUS_HAS_TO_ACTIVATE;			   			
    		if($this->checkNickAvailability($nick) || ($nick == '' && $this->_setting('no_nick_needed'))){
    			if(strpos($this->_setting('register.groups'), ':'.$group.':') !== false || $this->checkRight('administer_group', $group) &&
    			   ($status==User::STATUS_HAS_TO_ACTIVATE || $this->checkRight('administer_user'))){
    			   	if($pwd == $pwd2){
    			   		if($this->sp->ref('TextFunctions')->getPasswordStrength($pwd) >= $this->_setting('pwd.min_strength')){
    			   			if($email != '' && $this->sp->ref('TextFunctions')->isEmail($email)){
    			   				$activate_code = ($status == User::STATUS_HAS_TO_ACTIVATE) ? md5(time().$this->sp->ref('TextFunctions')->generatePassword(20, 10, 0, 0)): ''; 
		    			   		$id = $this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'user 
		    									(`nick`, `hash`, `group`, `email`, `status`, `created`, `last_login`, `activate`) VALUES 
		    									(\''.mysql_real_escape_string($nick).'\', 
		    										\''.$this->sp->ref('User')->hashPassword($pwd, $this->sp->ref('TextFunctions')->generatePassword(51, 13, 7, 7)).'\', 
		    										\''.mysql_real_escape_string($group).'\', 
		    										\''.mysql_real_escape_string($email).'\',
		    										\''.mysql_real_escape_string($status).'\',
		    										\''.mysql_real_escape_string(time()) .'\',
		    										\'-1\',
		    										\''.$activate_code.'\');');
		    			   		if($id !== false) {	
		    			   			$ok = true;	 
		    			   			foreach($data as $key=>$value) {
		    			   				$obj = $this->getUserDataById($key);
		    			   				// security check to not insert data for other groups
		    			   				if($obj->usedByGroup($group)){
		    			   					$x = ($this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'userdata_user 
		    			   										(`u_id`, `ud_id`, `value`, `last_change`) VALUES
		    			   										(\''.mysql_real_escape_string($id).'\',
		    			   										\''.mysql_real_escape_string($key).'\',
		    			   										\''.mysql_real_escape_string($value).'\', NOW());') == 0);
		    			   					$ok = $ok && $x;
		    			   				}
		    			   			}
		    			   			// create user album
		    			   			$this->sp->ref('Gallery')->createFolder($this->_setting('user.main_gallery'), 'user_'.$id, Gallery::STATUS_SYSTEM, true); // silent=true
		    			   			
		    			   			if($ok) {
			    			   			if($status == User::STATUS_HAS_TO_ACTIVATE){
			    			   				$mail = new ViewDescriptor($this->_setting('tpl.activation_mail'));
			    			   				
			    			   				$mail->addValue('nick', $nick);
			    			   				$mail->addValue('id', $id);
			    			   				$mail->addValue('email', $email);
			    			   				$mail->addValue('group', $group);
			    			   				$mail->addValue('pwd', $pwd);
			    			   				$mail->addValue('code', $activate_code);
			    			   				
			    			   				if($this->sp->ref('Mail')->send($email, $this->_('_Registered EMail'), $mail->render())){
			    			   					$this->_msg($this->_('New user created successfully', 'core'), Messages::INFO);
			    			   				} else {
			    			   					$this->_msg($this->_('Activation mail vould not be sent', 'core'), Messages::ERROR);
			    			   				}
			    			   			} else $this->_msg($this->_('New user created successfully', 'core'), Messages::INFO);
		    							return $id;
		    			   			} else {
		    			   				// delete every entered data
		    			   				$this->mysqlDelete('DELETE FROM '.$GLOBALS['db']['db_prefix'].'userdata_user WHERE u_id = "'.mysql_real_escape_string($id).'"');
		    			   				$this->mysqlDelete('DELETE FROM '.$GLOBALS['db']['db_prefix'].'user WHERE u_id = "'.mysql_real_escape_string($id).'"');
		    			   				$this->_msg($this->_('New user could not be created__', 'core'), Messages::ERROR);
		    							return false;
		    			   			}
		    					} else {
		    						$this->_msg($this->_('New user could not be created', 'core'), Messages::ERROR);
		    						return false;
		    					}
    			   			} else {
    			   				$this->_msg($this->_('Please enter a valid email'), Messages::ERROR);
    							return false;
    			   			}
    			   		} else {
    			   			$this->_msg($this->_('New Password is too weak', 'core'), Messages::ERROR);
    						return false;
    			   		}
    			   	} else {
    			   		$this->_msg($this->_('Different Passwords', 'core'), Messages::ERROR);
    					return false;
    			   	}
    			} else {
    				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        			return false;
    			}
    		} else { 
    			$this->_msg($this->_('_Nick not available', 'core'), Messages::ERROR);
        		return false;
    		}
    	}
    	
        /**
         * checks POST data and returnes true if all Data is valid
         * @param unknown_type $group
         */
    	public function checkRegisterData($group){
    		if(isset($_POST['ru_mail'])){
	    		$group = $this->getUserGroup($group);
//	    		$this->debugVar($_POST);
//	    		print_r($_POST);
	    		// first check if basic data is available
	    		if(isset($_POST['ru_mail']) && isset($_POST['ru_pwd_new']) && isset($_POST['ru_pwd_new2']) && $group != null) {
	    			// nick availability will be checked later
	    			
	    			// check extra user data
	    			$userData = $this->getUserDataForGroup($group);
	    			
	    			$ok = true;
	    			
	    			foreach($userData as $d){
// 	    				if($d->isForcedAtRegister()) print_r($d->getName());
	    				$ok = $ok && (($d->isForcedAtRegister() && isset($_POST['ru_ud'][$d->getId()]) && $_POST['ru_ud'][$d->getId()] != '') || !$d->isForcedAtRegister());
	    			}
	    			
	    			if(!$ok){
	    				$this->_msg($this->_('_Enter all data', 'core'), Messages::ERROR);
		        		return false;
	    			} else return true;
	    		} else {
	    			$this->_msg($this->_('_Enter all data', 'core'), Messages::ERROR);
		        	return false;
	    		}
    		}
    	}
    	
    	public function activateRegistration($code){
    		if($code!= '' && strlen($code) == 32){
    		echo 'asdf';
    			$g = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE activate="'.mysql_real_escape_string($code).'"');
				if($g !== false){
					$q = $this->mysqlUpdate('UPDATE '.$GLOBALS['db']['db_prefix'].'user SET activate="", status="'.User::STATUS_ACTIVE.'" WHERE activate="'.mysql_real_escape_string($code).'"');
					if($q !== false){
						$this->_msg($this->_('_Activation success', 'core'), Messages::INFO);
	        			return true;
					} else {
						$this->_msg($this->_('_Activation error', 'core'), Messages::ERROR);
	        			return false;
					}
				} else {
					$this->_msg($this->_('_Activation error', 'core'), Messages::ERROR);
        			return false;
				}
    		} else {
    			$this->_msg($this->_('_Activation error', 'core'), Messages::ERROR);
        		return false;
    		}
    	}
    	
    	public function rejectActivation($code){
    		if($code!= '' && strlen($code) == 32){
    			$g = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE activate="'.mysql_real_escape_string($code).'"');
				if($g !== false){
					if($this->mysqlDelete('DELETE FROM '.$GLOBALS['db']['db_prefix'].'user WHERE activate="'.mysql_real_escape_string($code).'"')) {
						$this->_msg($this->_('_Rejection success', 'core'), Messages::INFO);
	        			return true;
					} else {
						$this->_msg($this->_('_Rejection error', 'core'), Messages::ERROR);
	        			return false;
					}
				} else {
					$this->_msg($this->_('_Rejection error', 'core'), Messages::ERROR);
        			return false;
				}
    		} else {
    			$this->_msg($this->_('_Rejection error', 'core'), Messages::ERROR);
        		return false;
    		}
    	}
    	
    	public function setLastLogin(){
    		if($this->sp->ref('User')->isLoggedIn()){
    			$u = $this->sp->ref('User')->getLoggedInUser();
    			return $this->mysqlUpdate('UPDATE '.$GLOBALS['db']['db_prefix'].'user SET `last_login` = \''.mysql_real_escape_string(time()).'\' WHERE `id`=\''.mysql_real_escape_string($u->getId()).'\';');
    		} else return false;
    	}
    	
    	/**
    	 * deletes User by Id
    	 * @param $id
    	 */
    	public function deleteUser($id){
    		if($this->checkRight('administer_user')){
    			return $this->mysqlDelete('DELETE FROM '.$GLOBALS['db']['db_prefix'].'user WHERE id=\''.mysql_real_escape_string($id).'\';');
    		} else {
    			$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return false;
    		}
    	}
    	
    	/**
    	 * edits User by given id
    	 * @param $id
    	 * @param $nick
    	 * @param $pwd
    	 * @param $email
    	 * @param $status
    	 * @param $group
    	 * @param $userData
    	 */
    	public function editUser($id=-1, $nick='', $pwd='', $email='', $status=-1, $group=-1, $userData=array()){
    		if($id == -1) $id = $this->sp->ref('User')->getLoggedInUser()->getId();

    		if($id == $this->sp->ref('User')->getLoggedInUser()->getId() || $this->checkRight('administer_user', $id)){
    			
    			$query = array();
    			$err = false;
    			
    			// nick just can be changed by authorized uer and if available
    			if($nick != '' && $this->checkRight('administer_user')) {
    				if($this->checkNickAvailability($nick)){
    					$query[] = '`nick`="'.mysql_real_escape_string($nick).'"';
    				} else $this->_msg($this->_('Nick not available'), Messages::ERROR);
    			} else $nick = '';
    			
    			// accept email just if it is an email
    			if($email != '') {
    				if($this->sp->ref('TextFunctions')->isEmail($email)){
    					$query[] = '`email`="'.mysql_real_escape_string($email).'"';
    				} else {
    					$this->_msg($this->_('Please enter a valid email'), Messages::ERROR);
    					$err = true;
    				}
    			}
    			// create new password hash
    			if($pwd != '') {
    				if($this->sp->ref('TextFunctions')->getPasswordStrength($pwd) >= $this->_setting('pwd.min_strength')){
    					$salt = $this->sp->ref('TextFunctions')->generatePassword(51, 13, 7, 7);
    					$query[] = '`hash`="'.$this->sp->ref('User')->hashPassword($pwd, $salt).'"';
    				} else {
    					$this->_msg($this->_('New Password is too weak'), Messages::ERROR);
    					$err = true;
    				}
       			}
       			
       			if($status != -1 && $this->checkRight('administer_user')){
       				$query[] = '`status`="'.mysql_real_escape_string($status).'"';
       				if($status != User::STATUS_HAS_TO_ACTIVATE) $query[] = ' `activate`=""';
       			}
       			
       			if($group != -1 && $this->checkRight('administer_user')){
       				$query[] = '`group`="'.mysql_real_escape_string($group).'"';
       			}
       			
       			//TODO: userData
       			//$this->debug(implode(', ', $query));
       			       			
       			if(!$err) {
       				$q = $this->mysqlUpdate('UPDATE '.$GLOBALS['db']['db_prefix'].'user SET '.implode(', ', $query).' WHERE id="'.mysql_real_escape_string($id).'"');
       				if($q) {
       					//$this->_msg($this->_('Update successfull'), Messages::INFO);
       					if($id == $this->sp->ref('User')->getLoggedInUser()->getId()) $this->sp->ref('User')->updateActiveUsers();
       					return true;
       				} else {
       				//	$this->_msg($this->_('Update unsuccessfull'), Messages::INFO);
       					return false;
       				}
       			} else return false;
      		} else return false;
    	}
	}
?>