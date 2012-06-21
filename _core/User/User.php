<?php
	require_once('model/UserObject.php');
	require_once('model/UserGroup.php');
	require_once('model/UserDataHelper.php');
	
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
        
         function __construct(){
        	$this->name = 'User';
        	$this->ini_file = $GLOBALS['to_root'].'_core/User/User.ini';
            parent::__construct();
            $this->dataHelper = new UserDataHelper($this->settings);
            
            $this->viewAdmin = new UserAdminView($this->settings, $this->dataHelper);
            $this->viewFront = new UserFrontView($this->settings,  $this->dataHelper);
           	
            //load User Data From Session if available
            if(isset($_SESSION['User']) && isset($_SESSION['User']['loggedInUser'])){
            	$_SESSION['User']['viewingUser'] = $this->fixObject($_SESSION['User']['viewingUser']);
            	$_SESSION['User']['loggedInUser'] = $this->fixObject($_SESSION['User']['loggedInUser']);
            	
            	$this->loggedInUser = $_SESSION['User']['loggedInUser'];
            	if(isset($_SESSION['User']['viewingUser'])) $this->viewingUser = $this->fixObject($_SESSION['User']['viewingUser']);
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
          		default:
          			return 'idk';
          			break;
          	}  
         }
         
         public function admin($args){
            $chapter = (isset($args['chapter']) && $args['chapter'] != '') ? $args['chapter'] : '';
            $action = (isset($args['action']) && $args['action'] != '') ? $args['action'] : '';
            
            $page = (isset($args['page']) && $args['page'] > 0) ? $args['page'] : 1;
        	$id = (isset($args['id']) && $args['id'] > 0) ? $args['id'] : -1;
        	
            switch($chapter){
            	case 'usercenter':
            		break;
            	case 'edit_user':
            		break;
            	case 'new_user':
            		break;
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
            				$this->_msg($this->_('Wrong Parameters', 'core'), Messages::ERROR);
        					return false;
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
         			$email = isset($args['email']) ? $args['email'] : '';
         			$group = isset($args['group']) ? $args['group'] : '';
         			
         			return $this->register($nick, $pwd, $email, $group);
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
         
        /* functions */
        /**
         * returnes false if pwd is wrong
         * @param unknown_type $nick
         * @param unknown_type $pwd
         */
        private function rightPwd($nick, $pwd){
        	$hash = $this->dataHelper->getUserHashByNick($nick);
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
        		$u = $this->dataHelper->getUserByNick($nick);
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
         
    	public function register($nick, $pwd, $email, $group, $status=User::STATUS_HAS_TO_ACTIVATE){
        	return $this->dataHelper->register($nick, $pwd, $email, $group, $status);
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
         	$this->loggedInUser = $user;
         	$_SESSION['User']['loggedInUser'] = $user; 
         }
         
         /**
          * returnes logged in User
          */
         public function getLoggedInUser() {
         	return $this->loggedInUser;
         }
         /**
          * updates data for viewing and loggedin User
          */
         public function updateActiveUsers() {
         	$this->loggedInUser = $this->dataHelper->getUser($this->loggedInUser->getId());
         	$this->viewingUser = $this->dataHelper->getUser($this->viewingUser->getId());
         	
         	$_SESSION['User']['loggedInUser'] = $this->loggedInUser;
         	$_SESSION['User']['viewingUser'] = $this->viewingUser;
         }
         
         public function isLoggedIn() {
         	return $this->getLoggedInUser() != null;
         }
         
         /**
          * checks Session data for session expiration
          * Session will expire as defined in settings
          * Additionally ID regeneration will be triggered
          */
     	 public function checkSessionExpiration() {
        	// create session creation time
			if(!isset($_SESSION['User']['created_time'])) $_SESSION['User']['created_time'] = time();
	
			// regenerate session id after specified time	
        	if($this->_setting('session.regenerate_after') > -1 && time() - $_SESSION['User']['created_time'] > $this->_setting('session.regenerate_after')){
        		session_regenerate_id(true);    // change session ID for the current session an invalidate old session ID
    			$_SESSION['created_time'] = time();  // update creation time
        	}
        	        	
        	if($this->loggedInUser != null && isset($_SESSION['User']['last_activity']) && isset($_SESSION['User']['last_activity']) && (time() - $_SESSION['User']['last_activity']) > $this->_setting('session.idle_time')){

        		$this->logout();
        		$this->_msg($this->_('_session_expired'), Messages::ERROR);
        		$GLOBALS['session_expired'] = true;
        	}
        	
        	$_SESSION['User']['last_activity'] = time();
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
    }
 ?>