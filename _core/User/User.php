<?php
	require_once('model/UserObject.php');
	require_once('model/UserGroup.php');
	require_once('model/UserDataHelper.php');
	require_once('model/UserData.php');
	require_once('model/UserDataGroup.php');
	
	require_once('view/UserAdminView.php');
	require_once('view/UserFrontView.php');
	
    class User extends Service implements IService  {
        // MVC Objects
        private $dataHelper;
        private $viewAdmin;
        private $viewFront;
        
    	// User Objects
    	private $loggedInUser;
        private $viewingUser;
        
        // tmp Groups -> dataHandler
     //   private $groups;
        
        const STATUS_ACTIVE = 1;
        const STATUS_HAS_TO_ACTIVATE = 2;
        const STATUS_BLOCKED = 3;
        const STATUS_DELETED = 4;
        
        const DATA_TYPE_INT = 0;
        const DATA_TYPE_STRING = 1;
        const DATA_TYPE_CHECKBOX = 2;
        const DATA_TYPE_DROPDWN = 3;
        const DATA_TYPE_IMAGE = 4;
        const DATA_TYPE_EMAIL = 5;
        const DATA_TYPE_TEXT = 6;
        
        const VISIBILITY_HIDDEN = 0;
        const VISIBILITY_VISIBLE = 1;
        const VISIBILITY_FORCED = 2;
        
         function __construct(){
        	$this->name = 'User';
        	$this->ini_file = $GLOBALS['to_root'].'_core/User/User.ini';
            parent::__construct();
            $this->dataHelper = new UserDataHelper($this->settings);
            
            $this->viewAdmin = new UserAdminView($this->settings, $this->dataHelper);
            $this->viewFront = new UserFrontView($this->settings,  $this->dataHelper);
           	           	
            //$this->debugVar($_SESSION['User'] == null);
            //$this->debugVar($_SESSION['User']['loggedInUser'] == null);
            
            //load User Data From Session if available
            if(isset($_SESSION['User']) && isset($_SESSION['User']['loggedInUser'])){
            	$_SESSION['User']['viewingUser'] = $this->fixObject($_SESSION['User']['viewingUser']);
            	$_SESSION['User']['loggedInUser'] = $this->fixObject($_SESSION['User']['loggedInUser']);
            	
            	if(get_class($_SESSION['User']['loggedInUser']) != 'UserObject') $this->debugVar('fixObject ERROR');
            	
            	$this->loggedInUser = $_SESSION['User']['loggedInUser'];
            	
            	if(isset($_SESSION['User']['viewingUser'])) $this->viewingUser = $_SESSION['User']['viewingUser'];
            	else $this->viewingUser = $this->loggedInUser;
            	
            } else $this->loggedInUser = null;
            
         }
         
         private function fixObject (&$object) {
  			if (!is_object ($object) && gettype ($object) == 'object')
    			return ($object = unserialize (serialize ($object)));
  			return $object;
		}
         
        
         public function view($args) {
            $action = (isset($args['action'])) ? $args['action'] : '';
          	switch($action){
          		case 'login_form':
					$target = isset($args['target']) ? $args['target'] : '';
					return $this->viewFront->tplLogin($target);
					break;
          		case 'user_menu':
          			return $this->viewFront->tplUserMenu();
          			break;
          		case 'register_form':
          			$group = (isset($args['group'])) ? $args['group'] : '';
          			return $this->viewFront->tplRegister($group);
          			break;
          		case 'confirm_form':
          			return '--confirm--';
          			break;
          		default:
          			return 'idk';
          			break;
          	}  
         }
         
         public function admin($args){
         	/*$this->debugVar($this->loggedInUser);
         	$this->debugVar($_SESSION['User']);*/
         	
            $chapter = (isset($args['chapter']) && $args['chapter'] != '') ? $args['chapter'] : '';
            $action = (isset($args['action']) && $args['action'] != '') ? $args['action'] : '';
            
            $page = (isset($args['page']) && $args['page'] > 0) ? $args['page'] : 1;
        	$id = (isset($args['id']) && $args['id'] > 0) ? $args['id'] : -1;
        	
            switch($chapter){
            	case 'user':
            		return $this->viewAdmin->tplUser($page);
            		break;
            	case 'edit_user':
            		return $this->viewAdmin->tplUserEdit($id);
               		break;
            	case 'new_user':
            		return $this->viewAdmin->tplUserNew($id);
            		break;
            	case 'usergroup':
            		return 'usergroup';
            		break;
            	case 'edit_usergroup':
            		return 'edit_usergroup';
            		break;
            	case 'new_usergroup':
            		return 'new_usergroup';
            		break;
            	case 'userdata':
            		return $this->viewAdmin->tplUserData($page);
            		break;
            	/* --- profile --- */
            	case 'profile':
            		return $this->viewAdmin->tplProfile();
            		break;
            	case 'profile_data':
            		return $this->viewAdmin->tplProfileData();
            		break;
            	case 'profile_notifications':
            		return $this->viewAdmin->tplProfileNotifications();
            		break;
            	case 'profile_privacy':
            		return $this->viewAdmin->tplProfilePrivacy();
            		break;
            	default:
            		switch($action){
            			case 'unset_viewing_user':
            				$this->viewingUser = $this->loggedInUser;
          					$_SESSION['User']['viewingUser'] = $this->viewingUser;
          					return true;
               				break;
            			case 'set_viewing_user':
            				if($this->checkRight('can_change_viewing_user') && $id != -1){
            					$this->viewingUser = $this->dataHelper->getUser($id);
            					$_SESSION['User']['viewingUser'] = $this->viewingUser;
            					return true;
            				} else {
            					$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
            					return false;
            				}
            				break;
            			case 'edit_user_change_pwd':
            				$pwd = (isset($args['pwd'])) ? $args['pwd'] : '';
            				$pwd1 = (isset($args['pwd1'])) ? $args['pwd1'] : '';
            				$u = $this->dataHelper->getUser($id);
            				if($u != null && ($this->checkRight('administer_group', $u->getGroup()->getId()) || $this->checkRight('edit_user', $u->getId()))){
            					if($pwd == $pwd1){
            						if($this->dataHelper->editUser($u->getId(), '', $pwd)) {
            							$this->_msg($this->_('Password changed successfull'), Messages::INFO);
            							return true;
            						} else {
            							$this->_msg($this->_('Password could not be changed'), Messages::ERROR);
            							return false;
            						}
            					} else {
            						$this->_msg($this->_('Passwords dont match', 'core'), Messages::ERROR);
            						return false;
            					}
            				} else {
            					$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
            					return false;
            				}
            				break;
            			case 'profile_edit_email':
            				$email = (isset($args['email'])) ? $args['email'] : '';
            				return $this->dataHelper->editUser(-1, '', '', $email);
            				break;
            			case 'profile_change_pwd':
            				$pwd = (isset($args['pwd'])) ? $args['pwd'] : '';
            				$pwd1 = (isset($args['pwd1'])) ? $args['pwd1'] : '';
            				$pwd_old = (isset($args['pwd_old'])) ? $args['pwd_old'] : '';
            				
            				if($pwd == $pwd1){
            					if($this->rightPwd($this->loggedInUser->getNick(), $pwd_old)){
            						if($this->dataHelper->editUser(-1, '', $pwd)) {
            							$this->_msg($this->_('Password changed successfull'), Messages::INFO);
            							return true;
            						} else {
            							$this->_msg($this->_('Password could not be changed'), Messages::ERROR);
            							return false;
            						}
            					} else {
            						$this->_msg($this->_('Wrong Password', 'core'), Messages::ERROR);
            						return false;
            					}
            				} else {
            					$this->_msg($this->_('Passwords dont match', 'core'), Messages::ERROR);
            					return false;
            				}
            				break;
            			default:
        					return $this->viewAdmin->tplUsercenter();
            				break;
            		}
            		break;
            }
         }
          
         public function run($args){
         	$action = isset($args['action']) ? $args['action'] : '';
         	switch($action){
         		case 'login':
         			$nick = isset($args['nick']) ? $args['nick'] : '';
         			$pwd = isset($args['pwd']) ? $args['pwd'] : '';
         			
         			return $this->login($nick, $pwd);
         			break;
         		case 'logout':
         			return $this->logout();
         			break;
         		case 'register':
         			$nick = isset($args['nick']) ? $args['nick'] : '';
         			$pwd = isset($args['pwd']) ? $args['pwd'] : '';
         			$pwd2 = isset($args['pwd2']) ? $args['pwd2'] : '';
         			$email = isset($args['email']) ? $args['email'] : '';
         			$group = isset($args['group']) ? $args['group'] : '';
         			
         			return $this->register($nick, $email, $group, $pwd, $pwd2);
         			break;
         		case 'newUser':
         			$nick = isset($args['nick']) ? $args['nick'] : '';
         			$pwd = isset($args['pwd']) ? $args['pwd'] : '';
         			$pwd2 = isset($args['pwd2']) ? $args['pwd2'] : '';
         			$email = isset($args['email']) ? $args['email'] : '';
         			$group = isset($args['group']) ? $args['group'] : '';
         			$status = isset($args['status']) ? $args['status'] : '';
         			
    				if($this->checkRight('administer_user') && $this->checkRight('administer_group', $_POST['eu_group'])){
    					
    					$nId = $this->register($nick, $mail, $group, $pwd, $pwd2, $status);
    					if($nId !== false){
    						return $nId;
    					} else return false;
    				} else {
    					$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
    					return false;
    				}
         			break;
         		case 'activateRegistration':
         			$code = isset($args['code']) ? $args['code'] : '';
         			return $this->dataHelper->activateRegistration($code);
         			break;
         		case 'rejectRegistration':
         			$code = isset($args['code']) ? $args['code'] : '';
         			return $this->dataHelper->rejectRegistration($code);
         			break;
         	}
         }
         
        public function getSettings() { return $this->settings; }
         
		public function data($args){}
         
		public function setup(){
			$error = true;
			include_once('setup/setup.php');
        	return $error;
		}
    	/**
         * handles Post Variables in Admincenter
         */
        public function handleAdminPost(){
		    if(isset($_POST['action'])){
		    	switch($_POST['action']){
		    		case 'editUser':
		    			if(isset($_POST['eu_id']) && isset($_POST['eu_mail']) && isset($_POST['eu_status']) && isset($_POST['eu_group'])){
		    				$user = $this->dataHelper->getUser($_POST['eu_id']);
		    				
		    				if($this->checkRight('edit_user', $user->getId()) || $this->checkRight('administer_group', $user->getGroup()->getId())){
		    					//potential security risk -> check if authorized to set new group
		    					$group = $this->checkRight('administer_group', $_POST['eu_group']) ? $_POST['eu_group'] : -1;
		    					
		    					if($this->dataHelper->editUser($_POST['eu_id'], '', '',$_POST['eu_mail'], $_POST['eu_status'], $group, array())){
		    						$this->_msg($this->_('_User Update success', 'core'), Messages::INFO);

		    						header('Location: '.$_SERVER["HTTP_REFERER"].$_POST['back_link']);
			             			exit(0);
		    					} else $this->_msg($this->_('_User Update error', 'core'), Messages::ERROR);
		    				} else $this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
		    			}
		    			break;
		    		/*case 'upload':
		    			$this->executeNewProfileImage();
		    			break;*/
		    		case 'newUser':
		    			//TODO: save data if error 
		    			if(isset($_POST['eu_nick']) && isset($_POST['eu_mail']) && isset($_POST['eu_status']) && isset($_POST['eu_group']) && isset($_POST['eu_pwd_new']) && isset($_POST['eu_pwd_new2'])){
		    				if($this->checkRight('administer_user') && $this->checkRight('administer_group', $_POST['eu_group'])){
		    					
		    					$nId = $this->register($_POST['eu_nick'], $_POST['eu_mail'], $_POST['eu_group'], $_POST['eu_pwd_new'], $_POST['eu_pwd_new2'], $_POST['eu_status']);
		    					if($nId !== false){
		    						if(isset($_POST['back_link'])) header('Location: '.$_SERVER["HTTP_REFERER"].$_POST['back_link'].$nId.'/');
		    						else return true;
		    					}
		    				} else $this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
		    			}
		    			break;
		    		default:
		    			break;
		    	}
		    }
        }
         
        /* functions */
        /**
         * returnes false if pwd is wrong
         * @param unknown_type $nick
         * @param unknown_type $pwd
         */
        private function rightPwd($nick, $pwd){
        	$hash = ($this->_setting('no_nick_needed')) ? $this->dataHelper->getUserHashByEMail($nick) : $this->dataHelper->getUserHashByNick($nick);
        	if($hash != ''){
        		$salt = substr($hash, strpos($hash, '#')+1);

        		if($hash == $this->hashPassword($pwd, $salt)){  
        			return true;
        		} else return false;
        	} else return false;
        }
        /**
         * 
         * Logs in User by nick and pwd
         * @param $nick
         * @param $pwd
         */
        public function login($nick, $pwd){
        	if($this->rightPwd($nick, $pwd)){        			
        		$u = ($this->_setting('no_nick_needed')) ? $this->dataHelper->getUserByEMail($nick) : $this->dataHelper->getUserByNick($nick);
        		switch($u->getStatus()){
        			case self::STATUS_ACTIVE:
        				$this->setLoggedInUser($u);
		        		
		        		// regenerate Session ID to prevent Unautorized Access through Session Hijacking
		        		session_regenerate_id();
		        		
		        		// update creation time
		        		$_SESSION['User']['created_time'] = time();
		        		
		        		// set activation time
		        		$_SESSION['User']['last_activity'] = time();
		        		
		        		//check if default password is still used
		        		if($this->getLoggedInUser()->getGroup()->getName() == 'root' && $pwd=='root'){
		        			$_SESSION['User']['defaultPwd'] = 'true';
		        		}
		        		
		        		$this->dataHelper->setLastLogin();
		        		
		        		$this->_msg($this->_('_login success', 'core'), Messages::INFO);
		        		return true;
        				break;
        			case self::STATUS_BLOCKED:
        				$this->_msg($this->_('_login blocked', 'core'), Messages::ERROR);
        				return false;
        				break;
        			case self::STATUS_DELETED:
        				$this->_msg($this->_('_login deleted', 'core'), Messages::ERROR);
        				return false;
        				break;
        			case self::STATUS_HAS_TO_ACTIVATE:
        				$this->_msg($this->_('_login has to activate', 'core'), Messages::ERROR);
        				return false;
        				break;
        			default:
        				$this->_msg($this->_('_wrong pwd', 'core'), Messages::ERROR);
        				return false;
        				break;
        		}
        	} else {
        		$this->_msg($this->_('_wrong pwd', 'core'), Messages::ERROR);
        		return false;
        	}
        }
         
        /**
         * logges out current User
         */
		public function logout() {
         	$this->loggedInUser = null;
        	$_SESSION = array();
        	session_unset ();
        	session_destroy ();
        	
        	session_start();
        	return true;
         }
         
    	public function register($nick, $email, $group, $pwd, $pwd2, $status=User::STATUS_HAS_TO_ACTIVATE, $data=array()){
        	return $this->dataHelper->register($nick, $email, $group, $pwd, $pwd2, $status, $data);
        }
        
        /**
         * checks POST data and returnes true if all Data is valid
         * @param unknown_type $group
         */
        public function checkRegisterData($group){
        	return $this->dataHelper->checkRegisterData($group);
        }
         
        /**
         * returnes Viewing User if allowed
         */
         public function getViewingUser() {
         	if($this->checkRight('can_change_viewing_user')){
         		return $this->viewingUser;
         	} else return $this->getLoggedInUser();
         }
         /**
          * sets Viewing User
          * @param UserObject $u_id
          */
         public function setViewingUser(UserObject $user){
         	if($this->checkRight('can_change_viewing_user')){
         		$this->viewingUser = $user;
         		$_SESSION['User']['viewingUser'] = $user;
         	}
         }
         
         /**
          * sets viewng User by Id
          * @param $u_id
          */
         public function setViewingUserById($u_id){
         	$this->setViewingUser($this->dataHelper->getUser($u_id));
         }
         
         private function setLoggedInUser(UserObject $user) {
         	if($user != null){
         		$this->loggedInUser = $user;
         		$_SESSION['User']['loggedInUser'] = $user;
         	} 
         }
         
         /**
          * returnes logged in User
          */
         public function getLoggedInUser() {
         	if($this->loggedInUser == null){
         		if(isset($_SESSION['User']) && isset($_SESSION['User']['loggedInUser'])) $this->loggedInUser = $_SESSION['User']['loggedInUser'];
         	}
         	return $this->loggedInUser;
         }
         /**
          * updates data for viewing and loggedin User
          */
         public function updateActiveUsers() {
         	$this->setLoggedInUser($this->dataHelper->getUser($this->loggedInUser->getId()));
         	$this->setViewingUser($this->dataHelper->getUser($this->viewingUser->getId()));
         }
         
         public function isLoggedIn() {
         	//$this->debugVar($this->getLoggedInUser());
         	return $this->getLoggedInUser() != null;
         }
         
         /**
          * checks Session data for session expiration
          * Session will expire as defined in settings
          * Additionally ID regeneration will be triggered
          */
     	 public function checkSessionExpiration() {
     	 	if($this->isLoggedIn()){
	        	// create session creation time
				if(!isset($_SESSION['User']['created_time'])) $_SESSION['User']['created_time'] = -1;
				
				// regenerate session id after specified time	
	        	if($this->_setting('session.regenerate_after') > -1 && time() - $_SESSION['User']['created_time'] > $this->_setting('session.regenerate_after')){
	        		session_regenerate_id(true);    // change session ID for the current session an invalidate old session ID
	    			$_SESSION['User']['created_time'] = time();  // update creation time
	        	}
	        	        	
	        	if($this->loggedInUser != null && isset($_SESSION['User']['last_activity']) && isset($_SESSION['User']['last_activity']) && (time() - $_SESSION['User']['last_activity']) > $this->_setting('session.idle_time')){
	
	        		$this->logout();
	        		$this->_msg($this->_('_session_expired'), Messages::ERROR);
	        		$GLOBALS['session_expired'] = true;
	        	}
	        	
	        	$_SESSION['User']['last_activity'] = time();
     	 	}
        } 
   	 	
        /**
         * hashes Passwort by using TextFunctions hashString function
         * @param $pwd
         * @param $salt
         */
        public function hashPassword($pwd, $salt){
        	return $this->sp->ref('TextFunctions')->hashString($pwd, $salt, 'whirlpool');
        }
        
        /**
         * returnes array of all users
         */
        public function getUsers() {
        	return $this->dataHelper->getUsers(-1);
        }
        
        /**
         * returnes User by UserdataId and calue
         * @param unknown_type $data_id
         * @param unknown_type $value
         */
        public function getUserByData($data_id, $value){
        	return $this->dataHelper->getUserByData($data_id, $value);
        }
        
        /**
         * returnes UserData object by given id
         * is used by UserInfo->loadData(ServiceProvider $sp)
         * @param $id
         */
        public function getUserDataByUserId($id){
        	return $this->dataHelper->getUserDataByUserId($id);
        }
    }
 ?>