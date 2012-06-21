<?php
	class UserDataHelper extends TFCoreFunctions{
		protected $name = 'User';
		
		private $users;
		private $groups;
		
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
		/**
		 * returnes Users 
		 * @param unknown_type $page
		 * @param unknown_type $perPage
		 */
		public function getUsers($page=-1, $perPage=-1) {
			$return = array();
			$limit = '';
			$u1 = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user '.$limit);
			if($u1 != array()){
				foreach($u1 as $u) 
					$return[] = new UserObject($u['nick'], $u['id'], $u['email'], $this->getUserGroup($u['group']), $u['status']);
			}
			return $return;
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
				if($u->getNick == $nick) return $nick;
			}
			$u = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE nick="'.mysql_real_escape_string($nick).'"');
			if($u != array()){
				$this->users[$u['id']] = new UserObject($u['nick'], $u['id'], $u['email'], $this->getUserGroup($u['group']), $u['status']);
				return $this->users[$u['id']];
			}
			return null;
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
		
		/** ---  SETTER --- */
		/**
		 * registers user
		 * @param string $nick
		 * @param string $pwd
		 * @param string $email
		 * @param int $group
		 * @param int $status
		 */
    	public function register($nick, $pwd, $email, $group, $status=User::STATUS_HAS_TO_ACTIVATE){
    		if($this->checkNickAvailibility($nick)){
    			if(strpos($this->_setting('register.groups'), ':'.$group.':') !== false || $this->checkRight('administer_group', $group)){
    				if($this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'user 
    									(`nick`, `hash`, `group`, `email`, `status`) VALUES 
    									(\''.mysql_real_escape_string($nick).'\', 
    										\''.$this->sp->ref('User')->hashPassword($pwd, $this->sp->ref('TextFunctions')->generatePassword(51, 13, 7, 7)).'\', 
    										\''.mysql_real_escape_string($group).'\', 
    										\''.mysql_real_escape_string($email).'\',
    										\''.mysql_real_escape_string($status).'\');')) {
    				
    					$this->_msg($this->_('New user created successfully', 'core'), Messages::INFO);
    					return true;
    				} else {
    					$this->_msg($this->_('New user could not be created', 'core'), Messages::ERROR);
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

    		if($id == $this->sp->ref('User')->getLoggedInUser()->getId() || $this->checkRight('administer_user')){
    			
    			$query = array();
    			$err = false;
    			
    			// nick just can be changed by authorized uer and if available
    			if($nick != '' && $this->checkRight('administer_user')) {
    				if($this->checkNickAvailability($nick)){
    					$query[] = 'nick="'.mysql_real_escape_string($nick).'"';
    				} else $this->_msg($this->_('Nick not available'), Messages::ERROR);
    			} else $nick = '';
    			
    			// accept email just if it is an email
    			if($email != '') {
    				if($this->sp->ref('TextFunctions')->isEmail($email)){
    					$query[] = 'email="'.mysql_real_escape_string($email).'"';
    				} else {
    					$this->_msg($this->_('Please enter a valid email'), Messages::ERROR);
    					$err = true;
    				}
    			}
    			
    			// create new password hash
    			if($pwd != '') {
    				if($this->sp->ref('TextFunctions')->getPasswordStrength($pwd) >= $this->_setting('pwd.min_strength')){
    					$query[] = 'hash="'.$this->sp->ref('User')->hashPassword($pwd, $this->sp->ref('TextFunctions')->generatePassword(51, 13, 7, 7)).'"';
    				} else {
    					$this->_msg($this->_('New Password is too weak'), Messages::ERROR);
    					$err = true;
    				}
       			}
       			
       			if($status != -1 && $this->checkRight('administer_user')){
       				$query[] = 'status="'.mysql_real_escape_string($status).'"';
       			}
       			
       			if($group != -1 && $this->checkRight('administer_user')){
       				$query[] = 'group="'.mysql_real_escape_string($group).'"';
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