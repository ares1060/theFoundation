<?php
	require_once('model/UserInfo.php');
	require_once('model/UserGroup.php');
	require_once('model/UserData.php');
	require_once('model/UserDataGroup.php');
	
    class User extends Service implements IService  {
        private $user;
        private $groups;
        
        /*const GROUP_ADMIN = 1;
        const GROUP_USER = 2;
        const GROUP_MODERATOR = 3;
        const GROUP_GUEST = 4;*/
        
        const STATUS_ACTIVE = 1;
        const STATUS_HAS_TO_ACTIVATE = 2;
        const STATUS_BLOCKED = 3;
        const STATUS_DELETED = 4;
        
        const GROUP_TYPE_TEXT = 0;
        const GROUP_TYPE_CHECKBOX = 1;
        const GROUP_TYPE_INT = 2;
        const GROUP_TYPE_IMAGE = 3; 
        
        function __construct(){
        	$this->ini_file = $GLOBALS['to_root'].'_core/User/User.ini';
        	$this->name='User';
            parent::__construct();
            $this->user = array();
            $this->loadConfig($GLOBALS['config']['root'].'_core/User/User.config.php'); 
         //   $this->sp->run('Localization', array('load'=>$this->config['loc_file']));
        }
        public function getSettings() { return $this->settings; }
        
        /**
         *  
         */
        public function view($args) {
            $action=(isset($args['action'])) ? $args['action'] : '';
            if($action == 'login_form'){
                $target = isset($args['target']) ? $args['target'] : '';
                $render = new ViewDescriptor($this->config['tpl']['login_form']);
                $render->addValue('target', $target);
                return $render->render();
            } else if($action == 'user_actions'){
                if(isset($_SESSION['User'])){
                    $tmp = new ViewDescriptor($this->config['tpl']['ua_loggedin']);
                    return $tmp->render();
                } else {
                    $tmp = new ViewDescriptor($this->config['tpl']['ua_not_loggedin']);
                    return $tmp->render();
                }
            } 
            return '';
        }
        public function admin($args){
        	$chapter = (isset($args['chapter']) && $args['chapter'] != '') ? $args['chapter'] : '';
        	$page = (isset($args['page']) && $args['page'] > 0) ? $args['page'] : 1;
        	$id = (isset($args['id']) && $args['id'] > 0) ? $args['id'] : -1;
        	$iId = (isset($args['iId']) && $args['iId'] > 0) ? $args['iId'] : -1;
        	$action = (isset($args['action'])) ? $args['action'] : '';
        	$nick = (isset($args['nick'])) ? $args['nick'] : '';
        	
        	switch($chapter){
        		case 'user':
        			return $this->tplUCUser($page);
        			break;
        		case 'edit_user':
        			return $this->tplUCUserEdit($id);
        			break;
        		case 'new_user':
        			return $this->tplUCUserNew();
        			break;
        		case 'usergroup':
        			return $this->tplUCUsergroups($page);
        			break;
        		case 'edit_usergroup':
        			return 'edit usergroup';
        			break;
        		case 'new_usergroup':
        			return 'new usergroup';
        			break;
        		case 'userdata':
        			return $this->tplUCUserdata($page);
        			break;
        		case 'edit_userdata':
        			return 'edit userdata';
        			break;
        		case 'new_userdata':
        			return 'new userdata';
        			break;
        		default:
        			switch($action){
        				case 'setProfilePicture':
        					return $this->executeSetProfilePicture($id, $iId);
        					break;
        				case 'checkForm':
        					return $this->executeCheckForm($args);
        					break;
        				case 'checkNickAvailibility':
        					return $this->checkNickAvailibility($nick);
        					break;
        				case 'deleteUser':
        					return $this->deleteUser($id);
        					break;
        				default:
        					return $this->tplUsercenter();
        					break;
        			}
        			break;
        	}
            return '';
        }
        public function run($args){
            if(!isset($args['action'])) return false;
            $action = $args['action'];
            //Login
            if($action == 'login' && isset($args['nick']) && isset($args['pwd'])) {
            	return $this->login($args['nick'], $args['pwd']);
            }
            //Logout
            if($action == 'logout' && isset($_SESSION['User'])) {
            	return $this->logout();
            }
            //Register
            if($action == 'register' && isset($args['nick']) && isset($args['pwd']) && isset($args['email']) && isset($args['group'])){
                return $this->register($args['nick'], $args['pwd'], $args['email'], $args['group'], self::STATUS_HAS_TO_ACTIVATE);
            }
            //Delete 
            if($action == 'delete' && isset($args['id'])){
                return $this->delete($args['id']);
            }            
            //Update
            if($action == 'update' && isset($args['pwd']) && isset($args['email']) && isset($args['id'])){
            	return $this->update($args['id'], $args['pwd'], $args['email']);
            }
            return false;
        }
        
        /**
         * (non-PHPdoc)
         * @see _core/IService::data()
         * @return UserInfo
         */
        public function data($args){
            return $this->getUserInfo($args['id']);
        }  
        
        public function setup(){
        	//default user: Root:root (name:root)
        	
        	if(isset($GLOBALS['testDatabase']) && $GLOBALS['testDatabase']){
        		// delete old databases
        		$sql = '
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'user`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'userdata`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'userdatagroup`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'userdata_group`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'userdata_user`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'usergroup`;
        		';	
        		$this->mysqlMultipleSetup($sql);
        	}
        	$sql = '-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'user`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'user` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `nick` varchar(50) NOT NULL,
					  `pwd` varchar(32) NOT NULL,
					  `hash` varchar(180) NOT NULL,
					  `group` int(11) NOT NULL,
					  `email` varchar(100) NOT NULL,
					  `activate` varchar(100) NOT NULL,
					  `status` int(11) NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `nick` (`nick`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdata`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'userdata` (
					  `ud_id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(100) NOT NULL,
					  `desc` text NOT NULL,
					  `default` text NOT NULL,
					  `type` int(11) NOT NULL,
					  `visible` int(11) NOT NULL,
					  `help` text NOT NULL,
					  `g_id` int(11) NOT NULL,
					  PRIMARY KEY (`ud_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
					
					--
					-- Daten fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdata`
					--
					
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata` VALUES(1, \'Vorname\', \'\', \'\', 0, 1, \'\', 1);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata` VALUES(2, \'Nachname\', \'\', \'\', 0, 1, \'\', 1);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata` VALUES(3, \'Userimage\', \'\', \'1\', 3, 1, \'\', 2);
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdatagroup`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'userdatagroup` (
					  `g_id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(100) NOT NULL,
					  `beschreibung` text NOT NULL,
					  UNIQUE KEY `g_id` (`g_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
					
					--
					-- Daten fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdatagroup`
					--
					
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdatagroup` VALUES(1, \'Daten\', \'\');
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdatagroup` VALUES(2, \'Userimage\', \'\');
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdata_group`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'userdata_group` (
					  `g_id` int(11) NOT NULL,
					  `d_id` int(11) NOT NULL,
					  UNIQUE KEY `g_id` (`g_id`,`d_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
					
					--
					-- Daten fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdata_group`
					--
					
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(1, 1);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(1, 2);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(1, 3);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(2, 1);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(2, 2);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(2, 3);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(3, 1);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(3, 2);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(3, 3);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(4, 1);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(4, 2);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(4, 3);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(5, 1);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(5, 2);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_group` VALUES(5, 3);

					-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'userdata_user`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'userdata_user` (
					  `u_id` int(11) NOT NULL,
					  `d_id` int(11) NOT NULL,
					  `value` text NOT NULL,
					  UNIQUE KEY `u_id` (`u_id`,`d_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'usergroup`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'usergroup` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(100) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;
					
					--
					-- Daten fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'usergroup`
					--
					
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'usergroup` VALUES(1, \'root\');
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'usergroup` VALUES(2, \'admin\');
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'usergroup` VALUES(3, \'user\');
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'usergroup` VALUES(4, \'moderator\');
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'usergroup` VALUES(5, \'guest\');';
        	$b = $this->mysqlMultipleSetup($sql);
        	
        	$this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'user` VALUES (1, "root", "", "a3597971769fc171e38fb92ff3cd4cc429370b618342836ff7a2eb61fe7d6f70ead7dd6586c2044d759ab962b6fbb96d48981259e592e3c79b559d84a79fe64a#me:fpeH2cc68;p9npeQ/Qemi0UQ%Wu!g4Hweu=US4JsPUxqa-Oe", 1, "", "", 1);');
        	
        	$db_error = !($b);
        	if(!$db_error) {
        		$error = array();
        		
        		// create Rights
        		$error[] = $this->sp->ref('Rights')->addRight('User', 'usercenter');
        		$error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'usercenter', 1); // authorize Root to make administer Users
        		$error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'usercenter', 2); // authorize Root to make administer Users
        		
        		$error[] = $this->sp->ref('Rights')->addRight('User', 'edit_user');
        		$error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'edit_user', 1); // authorize Root to edit Users
   
        		$error[] = $this->sp->ref('Rights')->addRight('User', 'administer_group');
        		$error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'administer_group', 1); // authorize Root to create/edit and see any user
        		$error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'administer_group', 2); // authorize Admin to create/edit and see any user
        		$error[] = $this->sp->ref('Rights')->unauthorizeGroup('User', 'administer_group', 2, '1'); // unauthorize Admins to create/edit and see Roots    \__ Admins can only
        		$error[] = $this->sp->ref('Rights')->unauthorizeGroup('User', 'administer_group', 2, '2'); // unauthorize Admins to create/edit and see Admins   /   create Users, Moderators and Guests
        		
        		$error[] = $this->sp->ref('Rights')->addRight('User', 'edit_group');
        		$error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'edit_group', 1); // authorize Root to create groups
        		
        		$error[] = $this->sp->ref('Rights')->addRight('User', 'create_groups');
        		$error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'create_groups', 1); // authorize Root to create groups
        		
        		$error[] = $this->sp->ref('Rights')->addRight('User', 'create_data');
        		$error[] = $this->sp->ref('Rights')->authorizeGroup('User', 'create_data', 1); // authorize Root to create groups
        		
        		return true;
        	} else return false;
        }
        
        /**
         * handles Post Variables in Admincenter
         */
        public function handleAdminPost(){
        	/* handle _POST */
		    if(isset($_POST['action'])){
		    	switch($_POST['action']){
		    		case 'editUser':
		    			$this->executeEditUser();
		    			break;
		    		case 'upload':
		    			$this->executeNewProfileImage();
		    			break;
		    		case 'newUser':
		    			$this->executeNewUser();
		    			break;
		    		default:
		    			break;
		    	}
		    }
        }
        
        public function login($nick, $pwd){
        	$u = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE nick="'.mysql_real_escape_string($nick).'"');
	       	if($u != '' && $u != array() && isset($u['hash'])){
        		$salt = substr($u['hash'], strpos($u['hash'], '#')+1);

        		if($u['hash'] == $this->hashPassword($pwd, $salt)){        			
	        	//if($this->sp->db->exists('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE nick=\''.mysql_real_escape_string($nick).'\' AND pwd=\''.$this->hashPassword($pwd).'\';')){
	        		$array = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE nick=\''.mysql_real_escape_string($nick).'\';');
	        		switch($array['status']){
	        			case self::STATUS_ACTIVE:
	        				$this->user[$array['id']] = new UserInfo($array['nick'], $array['id'], $array['email'], $array['group'], $array['status']);
			        		$_SESSION['User']['id'] = $this->user[$array['id']]->getID();
			        		$_SESSION['User']['nick'] = $this->user[$array['id']]->getNick();
			        		$_SESSION['User']['group'] = $this->user[$array['id']]->getGroup();
			        		
			        		// regenerate Session ID to prevent Unautorized Access through Session Hijacking
			        		session_regenerate_id();
			        		
			        		// update creation time
			        		$_SESSION['created_time'] = time();
			        		
			        		// set activation time
			        		$_SESSION['last_activity'] = time();
			        		
			        		//check if default password is still used
			        		if($_SESSION['User']['nick']=='root' && $pwd=='root'){
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
        	} else {
        		$this->_msg($this->_('_wrong pwd', 'core'), Messages::ERROR);
        		return false;
        	}
        }
        
        public function logout(){
        	/* -- save history -- */
        	$history = array('prev_page'=>$_SESSION['history']['prev_page'], 'active_page'=>$_SESSION['history']['active_page']);
        	
        	$this->user[$_SESSION['User']['id']] = null;
        	$_SESSION = array();
        	session_unset ();
        	session_destroy ();
        	
        	/* -- restore history -- */
        	session_start();
        	$_SESSION['history'] = $history;
        	return true;
        }
        
    	public function register($nick, $pwd, $email, $group, $status){
    		if($this->checkNickAvailibility($nick)){
    			
    			if($this->checkRight('administer_group', $group)){
    				if($this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'user 
    									(`nick`, `hash`, `group`, `email`, `status`) VALUES 
    									(\''.mysql_real_escape_string($nick).'\', 
    										\''.$this->hashPassword($pwd, $this->sp->ref('TextFunctions')->generatePassword(51, 13, 7, 7)).'\', 
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
    	
    	public function delete($id){
    		if(!$this->sp->db->exists('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE id=\''.mysql_real_escape_string($id).'\';')) return false;
    		else {
    			return $this->sp->db->bool('DELETE FROM '.$GLOBALS['db']['db_prefix'].'user WHERE id=\''.mysql_real_escape_string($id).'\';');
    		}
    	}
    	
    	/*public function update($id, $pwd, $email, $fields){
    		if(!$this->sp->db->exists('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE id=\''.$id.'\';')) return false;
    		else {
    			if($this->sp->db->bool('UPDATE '.$GLOBALS['db']['db_prefix'].'user SET pwd=\''.$this->hashPassword($pwd).'\', \''.mysql_real_escape_string($email).'\');')){
    				return $this->sp->db->lazyUpdate($GLOBALS['db']['db_prefix'].'userdata', 'user_id = \''.$id.'\'', $fields);
    			} else {
    				return false;
    			}
    		}
    	}*/
        /*
    	public function addDataField($name, $type){
    		if($this->sp->db->exists('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'userdatafields WHERE name=\''.mysql_real_escape_string($name).'\';')) return false;
    		else {
    			if($this->sp->db->bool('ALTER TABLE `'.$GLOBALS['db']['db_prefix'].'userdata` ADD `'.mysql_real_escape_string($name).'` VARCHAR(500);')){
    				return $this->sp->db->bool('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdatafields` (`id`, `name`, `type`) VALUES (\'\', \''.mysql_real_escape_string($name).'\', \''.mysql_real_escape_string($type).'\');');
    			} else {
    				return false;
    			}
    		}
    	}
    	
    	public function editDataField($name, $type){
    		if($this->sp->db->exists('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'userdatafields WHERE name=\''.mysql_real_escape_string($name).'\';')) return false;
    		else {
				return $this->sp->db->bool('UPDATE '.$GLOBALS['db']['db_prefix'].'userdatafields SET type = \''.mysql_real_escape_string($type).'\' WHERE name=\''.mysql_real_escape_string($name).'\';');
    		}
    	}
    	
    	public function removeDataField($name){
    		if($this->sp->db->exists('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'userdatafields WHERE name=\''.mysql_real_escape_string($name).'\';')) return false;
    		else {
    			if($this->sp->db->bool('ALTER TABLE `'.$GLOBALS['db']['db_prefix'].'userdatafields` DROP `'.mysql_real_escape_string($name).'`;')){
    				return $this->sp->db->bool('DELETE FROM '.$GLOBALS['db']['db_prefix'].'userdatafields WHERE name=\''.mysql_real_escape_string($name).'\';');
    			} else {
    				return false;
    			}
    		}
    	}
    	*/
    	
    	/**
    	 * returnes UserInfo object by given id
    	 * @param $id
    	 */
    	public function getUserInfo($id){
    		if(!isset($this->user[$id])) {
    			$array = $this->sp->db->data(array('query'=>'SELECT * FROM '.$GLOBALS['db']['db_prefix'].'user WHERE id=\''.mysql_real_escape_string($id).'\';'));
    			$this->user[$id] = new UserInfo($array['nick'], $array['id'], $array['email'], $array['group'], $array['status']);
    		}
    		return $this->user[$id];
    	}
    	
    	/**
    	 * returnes UserInfo by Nick
    	 * @param $nick
    	 */
    	public function getUserInfoByNick($nick) {
    		$a = $this->mysqlRow('SELECT id FROM '.$GLOBALS['db']['db_prefix'].'user WHERE nick=\''.mysql_real_escape_string($nick).'\'');
    		if(is_array($a) && isset($a['id'])) return $this->getUserInfo($a['id']);
    		else return null;
    	}
    	
    	/**
    	 * returnes UserData object by given id
    	 * is used by UserInfo->loadData(ServiceProvider $sp)
    	 * @param $id
    	 */
    	public function getUserData($id){
    		$user = $this->getUserInfo($id);
    		$data = $this->mysqlArray('SELECT *, udg.name as gName, ud.name as dName FROM `'.$GLOBALS['db']['db_prefix'].'userdata` ud
    										LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'userdatagroup` udg ON ud.g_id = udg.g_id
    										LEFT JOIN (SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'userdata_user` WHERE u_id="'.mysql_real_escape_string($user->getId()).'") uud ON uud.d_id = ud.ud_id
    										LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'userdata_group` ud_g ON ud.ud_id = ud_g.d_id
    										WHERE ud_g.g_id = "'.mysql_real_escape_string($user->getGroupId()).'"');
    		$aData = array();

    		if($data != array() && $data != '') {
    			foreach($data as $dat){
    				if(!isset($aData[$dat['name']])) {
    					$aData[$dat['gName']] = new UserDataGroup($dat['g_id'], $dat['gName'], $dat['beschreibung']);
    					/*$aData[$dat['gName']] = array();
    					$aData[$dat['gName']]['data'] = array();
    					$aData[$dat['gName']]['id'] = $dat['g_id'];
    					$aData[$dat['gName']]['desc'] = $dat['beschreibung'];*/
    				}
    				$aData[$dat['gName']]->addData(new UserData($dat['d_id'], $dat['dName'], $dat['desc'], $dat['help'], $dat['value'], $dat['type']));
    			}
    		}

    		return $aData;
    	}
    	
    	/**
    	 * returnes all UserData by page
    	 * @param $page
    	 */
    	public function getAllUserData($page=1) {
    		if(!isset($GLOBALS['User']['groups'])) self::loadUserGroups();
    		
    		$all = $this->getAllUserdataCount();
        	$from = ($page-1)*($this->config['userdata_per_page']);
        	
        	if($from > $all) $from = 0;
        	
        	$limit = ($page == -1) ? '' : 'LIMIT '.mysql_real_escape_string($from).', '.mysql_real_escape_string($this->config['userdata_per_page']).';';
    		
    		$groups = $selects = '';
    		foreach($GLOBALS['User']['groups'] as $group) {
    			//$groups .= 'LEFT JOIN (SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'userdata_group` WHERE g_id="'.mysql_real_escape_string($group->getId()).'") ug'.mysql_real_escape_string($group->getId()).' ON u.ud_id = ug.d_id ';
    			$groups .= ' IF( u.ud_id IN (SELECT d_id FROM `'.$GLOBALS['db']['db_prefix'].'userdata_group` WHERE g_id="'.mysql_real_escape_string($group->getId()).'"), true, false) usedBy_'.mysql_real_escape_string($group->getId()).', ';
    			//$selects .= 'ug'.mysql_real_escape_string($group->getId()).'.ud_id '.$group->getName().', ';
    		}
    		
    		$data = $this->mysqlArray('SELECT '.$groups.'g.name dgroupname, g.g_id dgroupid, g.beschreibung dgroupdesc, u.ud_id data_id, u.name data_name,
    										u.desc data_desc, u.help data_help, u.default data_val, u.type data_type
    										FROM `'.$GLOBALS['db']['db_prefix'].'userdata` u
    										LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'userdatagroup` g ON u.g_id = g.g_id  ORDER BY g.g_id '.$limit);
    		
    		$return = array();
    		if($data != array() && $data != ''){
    			foreach($data as $d){
    				if(!isset($return[$d['dgroupname']])) $return[$d['dgroupname']] = new UserDataGroup($d['dgroupid'], $d['dgroupname'], $d['dgroupdesc']);
    				$newd = new UserData($d['data_id'], $d['data_name'], $d['data_desc'], $d['data_help'], $d['data_val'], $d['data_type']);
    				foreach($GLOBALS['User']['groups'] as $group) {
    					if($d['usedBy_'.$group->getId()] == 1){
    						$newd->isUsedByGroup($group->getId());
    					} 
    				}
    				$return[$d['dgroupname']]->addData($newd);
    				unset($newd);
    			}
    		}	
    		return $return;		
    	}
    	
    	/**
    	 * loads User group in global array
    	 */
    	public static function loadUserGroups() {
           	$sql = 'SELECT `id`, `name` FROM `'.$GLOBALS['db']['db_prefix'].'usergroup` ';

           	$GLOBALS['User']['groups'] = array();
        	
        	$result = mysql_query($sql);
            if($result){
            	
	            while($row = mysql_fetch_assoc($result)){
	            	$GLOBALS['User']['groups'][$row['id']] = new UserGroup($row['id'], $row['name']);
	            }
            }
    	}
    	
    	/**
    	 * returnes User Group for given Groupname
    	 * @param string $string
    	 */
        public static function getUserGroup($string){
        	if(!isset($GLOBALS['User']['groups']) || $GLOBALS['User']['groups'] == array()) User::loadUserGroups();
        	
        	//$a = array_flip($GLOBALS['User']['groups']);
        	foreach($GLOBALS['User']['groups'] as $g){
        		if($g->getName() == $string) return $g->getId();
        	}
        	return null;
        }
        
    	/**
    	 * returnes User Group for given Groupid
    	 * @param int $id
    	 */
        public static function getUserGroupNameFromId($id){
        	if(!isset($GLOBALS['User']['groups']) || $GLOBALS['User']['groups'] == array()) User::loadUserGroups();

        	return isset($GLOBALS['User']['groups'][$id]) ? $GLOBALS['User']['groups'][$id]->getName() : '';
        }
                
        /**
         * returnes hashed Password
         * uses wirlpool hashing and a salt
         * @param $pwd
         */
        private function hashPassword($pwd, $salt){
        	return $this->sp->ref('TextFunctions')->hashString($pwd, $salt, 'whirlpool');//hash('whirlpool', $salt.$pwd).'#'.$salt;
        }
        
        /* =======      DATA FUNCTIONS     ========= */
        /**
         * returnes all Users 
         * @param $page if $page = -1 all Users will be returned
         * @param $groups all Groups will be returned
         */
        private function getAllUser($page=-1, $groups=array()) {
        	$all = $this->getAllUserCount(-1, -1);
        	$from = ($page-1)*($this->config['user_per_page']);
        	
        	if($from > $all) $from = 0;
        	
        	$limit = ($page == -1) ? '' : 'LIMIT '.mysql_real_escape_string($from).', '.mysql_real_escape_string($this->config['user_per_page']).';';
        	
        	// create group filter
        	if($groups != array()) {
        		foreach($groups as $id){
        			$group[] = '`group`="'.mysql_real_escape_string($id).'"';
        		}
        		$group = 'WHERE '.implode(' OR ', $group);
        	} else $group = '';
        	
        	// add own id
        	if($group != '') $group .= ' OR `id`="'.$_SESSION['User']['id'].'" ';
        	
        	$query = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'user` '.$group.' ORDER BY id desc '.$limit);

        	$return = array();
        	        	
        	if($query != array() && $query != ''){
        		foreach($query as $u){
        			$return[] = new UserInfo($u['nick'], $u['id'], $u['email'], $u['group'], $u['status']);
        		}
        	}

        	return $return;
        }
        
        /**
         * returnes Usercount
         * @param $status
         */
        private function getAllUserCount($status=-1, $gr=-1) {
        	$status = ($status != -1) ? ' WHERE `status`="'.mysql_real_escape_string($status).'"' : '';
        	$group = ($gr != -1) ? ' WHERE `group`="'.mysql_real_escape_string($gr).'"' : '';
        	
        	$query = $this->mysqlRow('SELECT COUNT(*) count FROM `'.$GLOBALS['db']['db_prefix'].'user` '.$status.' '.$group);

        	if($query) return $query['count'];
        	else return -1;
        }
        
    	/**
         * returnes Userdatacount
         * @param $status
         */
        private function getAllUserdataCount() {
        	
        	$query = $this->mysqlRow('SELECT COUNT(*) count FROM `'.$GLOBALS['db']['db_prefix'].'userdata` ');

        	if($query) return $query['count'];
        	else return -1;
        }
    
    	/**
    	 * returns UserGroups per Page 
    	 * just used at the admincenter -> use User::loadUserGroups for normal need of usergroups instead 
    	 */
        private function getUserGroups($page = -1){
       		if(!isset($GLOBALS['User']['groups'])) self::loadUserGroups();
        	
        	$from = ($page == -1) ? 0 : ($page-1)*$this->config['usergroup_per_page'];
        	$length = ($page == -1) ? $this->getAllUserGroupsCount() : $this->config['usergroup_per_page'];
        	
        	if($from > $this->getAllUserGroupsCount()) $from = 0;

        	return array_slice($GLOBALS['User']['groups'], $from, $length);
        }
        
        /**
         * returnes count of all UserGroups
         */
        private function getAllUserGroupsCount(){
        	if(!isset($GLOBALS['User']['groups'])) self::loadUserGroups();
        	return count($GLOBALS['User']['groups']);
        }
        
        /**
         * checks Nick Availibility
         * @param $nick
         */
        public function checkNickAvailibility($nick) {
        	//$this->debugVar($this->getUserInfoByNick($nick));
        	return ($this->getUserInfoByNick($nick) === NULL);
        }
        
        /**
         * returnes if loggedin User is Allowed to acces Group $id or any group 
         * @param $id
         */
        private function isAllowedToAdministerAnyGroup(){
        	if(!isset($GLOBALS['User']['groups']) || $GLOBALS['User']['groups'] == array())User::loadUserGroups();
        	
        	// see if User is allowed to administer any group
        	$aut = $this->checkRight('administer_group');

        	if(!$aut) {
        		foreach($GLOBALS['User']['groups'] as $group){
        			$aut = $aut || $this->checkRight('administer_group', $group->getId());
        		}
        	}
        	return $aut;

        }
        
       
        /* =======    EDIT/NEW/DELETE  FUNCTIONS     ========= */
        /**
         * updates user in database
         * @param unknown_type $id
         * @param unknown_type $email
         * @param unknown_type $group
         * @param unknown_type $status
         * @param unknown_type $newpwd
         * @param unknown_type $data
         */
        public function editUser($id, $email, $group=-1, $status=-1, $newpwd='', $data=null){
        	$user = $this->getUserInfo($id);
        	if(($this->checkRight('edit_user', $user->getId()) || $this->checkRight('administer_group', $user->getGroupId())) && 
        		($group == $user->getGroupId() || $this->checkRight('administer_group', $group) || $group == -1)){
        		
        		
        		if($group == -1 || $this->checkRight('administer_group', $group) || $this->checkRight('edit_user', $user->getId())) {
        			
        			 	$status = ($status != -1 && !(isset($_SESSION['User']) && isset($_SESSION['User']['id']) && $_SESSION['User']['id'] == $id)) ? ', `status`="'.mysql_real_escape_string($status).'" ' : '';
        			 	$group = ($group != -1 && !(isset($_SESSION['User']) && isset($_SESSION['User']['id']) && $_SESSION['User']['id'] == $id)) ? ', `group`="'.mysql_real_escape_string($group).'" ' : '';
        			 	
        			 	$pwd = ($newpwd != '') ? ', `hash`="'.$this->hashPassword($newpwd, $this->sp->ref('TextFunctions')->generatePassword(51, 13, 7, 7)).'" ' : ''; 
        			 	
        			 	$error = array();

        			 	$error[] = !($this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'user` SET 
        									`email`="'.mysql_real_escape_string($email).'"
        									'.$group.$status.$pwd.' WHERE `id`="'.mysql_real_escape_string($id).'"') !== false);
        			 	
        			 	if(!in_array(true, $error) && is_array($data) && $data != null){
        			 		foreach($data as $k=>$d){
                 			 	$error[] = !$this->editData($id, $k, $d);
        			 		}
        			 	}

        			 	if(!in_array(true, $error)){
        			 		if($_SESSION['User']['nick'] == 'root' && isset($_SESSION['User']['defaultPwd']) && $newpwd != '' && $newpwd != 'root') unset($_SESSION['User']['defaultPwd']);
        			 		return true;
        			 	} else return false;
        		}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return false;
        	}
        }
    	
        /**
         * Deletes User and connected userdata
         * @param $id
         */
        public function deleteUser($id) {
        	$user = $this->getUserInfo($id);
        	if($this->checkRight('edit_user', $user->getId()) || $this->checkRight('administer_group', $user->getGroupId())){
        		//delete data
        		if($this->deleteUserDataForUser($id)){
        			if($this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'user` WHERE `id`="'.mysql_real_escape_string($id).'"')){
        				$this->_msg($this->_('Delete User success', 'core'), Messages::INFO);
        				return true;
        			} else {
        				$this->_msg($this->_('Delete User error', 'core'), Messages::ERROR);
        				return false;
        			}
        		} else {
        			$this->_msg($this->_('Delete User error', 'core'), Messages::ERROR);
        			return false;
        		}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return false;
        	}
        }
        
        /**
         * deletes user data for given User id
         * @param $id
         */
        public function deleteUserDataForUser($id) {
        	$user = $this->getUserInfo($id);
        	if($this->checkRight('edit_user', $user->getId()) || $this->checkRight('administer_group', $user->getGroupId())){
        		return $this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'userdata_user` WHERE `u_id`="'.mysql_real_escape_string($id).'"');
        	} else {
        		$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return false;
        	}
        }
        
        /**
         * updates or inserts user data in database
         * @param u_id
         * @param d_id
         * @param value
         */
        public function editData($u_id, $d_id, $value){
        	$user = $this->getUserInfo($u_id);
        	
        	if($this->checkRight('edit_user', $u_id) || $this->checkRight('administer_group', $user->getGroupId())){
        		$data = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'userdata_user` WHERE `u_id`="'.mysql_real_escape_string($u_id).'" AND `d_id`="'.mysql_real_escape_string($d_id).'"');
        		
          		if($data != array()){
        			return ($this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'userdata_user` SET 
        													`value`="'.mysql_real_escape_string($value).'" 
        												WHERE `u_id`="'.mysql_real_escape_string($u_id).'" 
        													AND `d_id`="'.mysql_real_escape_string($d_id).'"') !== false);
        		} else {
        			return ($this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'userdata_user` (`u_id`, `d_id`, `value`) 
        												VALUES ("'.mysql_real_escape_string($u_id).'", 
        														"'.mysql_real_escape_string($d_id).'", 
        														"'.mysql_real_escape_string($value).'")') !== false);
        		}
        	} else return false;
        }
        
        /**
         * adds file uploads to gallery album of the user
         * @param $id
         */
        public function uploadToUserFolder($id){
        	$user = $this->getUserInfo($id);
        	if($this->checkRight('edit_user', $user->getId()) || $this->checkRight('administer_group', $user->getGroupId())){
        		$_POST['album'] = $this->sp->ref('Gallery')->getAlbumId('u_'.$id);
        		$_POST['folder'] = $this->sp->ref('Gallery')->getFolderByNameAndAlbum('Userimage', $_POST['album'])->getId();
        		
        		$iId = $this->sp->ref('Gallery')->executeUploads(true);// true to supress messages

        		if($iId != array()){
        			return $this->editData($id, $this->config['UserImage_data_id'], $iId[0]);
        		}
        	} else $this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        	return false;
        }
        
     	/**
         * Create a GalleryAlbum for the User with id $id
         * @param $id
         */
        private function createUserAlbum($id) {
        	return $this->sp->ref('Gallery')->newAlbum('u_'.id, '', Gallery::STATUS_USER_ALBUM);
        }
        /* =======    POST  FUNCTIONS     ========= */
   
        /**
         * handles edit user
         */
        public function executeEditUser() {
        	if(isset($_POST['eu_id']) && isset($_POST['eu_mail']) && isset($_POST['eu'])){
        		
        		$user = $this->getUserInfo($_POST['eu_id']);
        		if($this->checkRight('edit_user', $user->getId()) || $this->checkRight('administer_group', $user->getGroup())){
        			$newpwd = (isset($_POST['eu_pwd_new']) && isset($_POST['eu_pwd_new2']) && $_POST['eu_pwd_new'] == $_POST['eu_pwd_new2']) ? $_POST['eu_pwd_new'] : '';

        			if($this->editUser($_POST['eu_id'], $_POST['eu_mail'], (isset($_POST['eu_group']) ? $_POST['eu_group']: -1), (isset($_POST['eu_status']) ? $_POST['eu_status']: -1), $newpwd , $_POST['eu'])) {
	        			$this->_msg($this->_('_User Update success', 'core'), Messages::INFO);
	        		} else $this->_msg($this->_('_User Update error', 'core'), Messages::ERROR);
        		} else $this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        	}
        }
        
        public function executeNewUser() {
        	if(isset($_POST['eu_nick']) && 
        		isset($_POST['eu_group']) && 
        		isset($_POST['eu_mail']) && 
        		isset($_POST['eu_status']) && 
        		isset($_POST['eu_pwd_new']) && 
        		isset($_POST['eu_pwd_new2'])) {
        			
        		$error = false;
        		if($_POST['eu_pwd_new'] !== $_POST['eu_pwd_new2'] || $_POST['eu_pwd_new'] === '') {
	        		$this->_msg($this->_('_Password is not the same', 'core'), Messages::ERROR);
	        		$error = true;
	        	}
				if($_POST['eu_pwd_new'] != '' && $_POST['eu_pwd_new'] == $_POST['eu_pwd_new2'] && $this->sp->ref('TextFunctions')->getPasswordStrength($_POST['eu_pwd_new']) < $this->config['min_pwd_strength']) {
	        		$this->_msg($this->_('_Password is too weak', 'core'), Messages::ERROR);
	        		$error = true;
	        	}
	        	
        		return !$error && $this->register($_POST['eu_nick'], $_POST['eu_pwd_new'], $_POST['eu_mail'], $_POST['eu_group'], $_POST['eu_status']);
        	}
        }
        
        /**
         * handles profile picture upload
         */
        public function executeNewProfileImage() {
        	if(isset($_POST['eu_id'])) {
        		$user = $this->getUserInfo($_POST['eu_id']);
        		if($this->checkRight('edit_user', $user->getId()) || $this->checkRight('administer_group', $user->getGroup())){
        			if($this->uploadToUserFolder($user->getId())){
        				$this->_msg($this->_('Userimage upload success', 'core'), Messages::INFO);
        			} else {
        				$this->_msg($this->_('Userimage upload error', 'core'), Messages::ERROR);
        			}
        		} // message will be gereraten at uploadToUserFolder
        		
        	} 
			
        }
        
        /**
         * Sets new Profile Picture
         * @param unknown_type $id
         * @param unknown_type $iId
         */
        public function executeSetProfilePicture($id, $iId) {
        	$user = $this->getUserInfo($id);
        	if($this->checkRight('edit_user', $user->getId()) || $this->checkRight('administer_group', $user->getGroup())){
        		if($this->editData($id, $this->config['UserImage_data_id'], $iId)){
        			$this->_msg($this->_('Profile picture set successfully', 'core'), Messages::INFO);
        		} else $this->_msg($this->_('Profile picture could not be set', 'core'), Messages::ERROR);
        	} else return false;
        }
        
        /**
         * returnes if submitted Form is valid
         * @param $args
         */
        public function executeCheckForm($args) {
        	$nick = isset($args['form']['nick']) ? $args['form']['nick'] : '';
        	$email = isset($args['form']['email']) ? $args['form']['email'] : '';
        	$pwd  = isset($args['form']['pwd'])? $args['form']['pwd'] : '';
        	$pwd2  = isset($args['form']['pwd2'])? $args['form']['pwd2'] : '';
        	
        	$error = false;
        	
        	// check password
        	if($pwd !== $pwd2 || $pwd === '') {
        		$this->_msg($this->_('_Password is not the same', 'core'), Messages::ERROR);
        		$error = true;
        	}
			if($pwd != '' && $pwd == $pwd2 && $this->sp->ref('TextFunctions')->getPasswordStrength($pwd) < $this->config['min_pwd_strength']) {
        		$this->_msg($this->_('_Password is too weak', 'core'), Messages::ERROR);
        		$error = true;
        	}
        	
        	// check nick
        	if($nick == ''){
        		$this->_msg($this->_('_no Nick', 'core'), Messages::ERROR);
        		$error = true;
        	}
        	if($nick != '' && !$this->checkNickAvailibility($nick)) {
        		$this->_msg($this->_('_Nick not available', 'core'), Messages::ERROR);
        		$error = true;
        	}
        	
        	// check email
        	if($email == '') {
        		$this->_msg($this->_('_no Email', 'core'), Messages::ERROR);
        		$error = true;
        	}
        	if(!$this->sp->ref('TextFunctions')->isEmail($email) && $email != ''){
        		$this->_msg($this->_('_wrong Email', 'core'), Messages::ERROR);
        		$error = true;
        	}
        	
        	if($error) {
        		return false;
        	} else {
        		return true;
        	}
        }
        
        /* =======    TEMPLATE FUNCTIONS     ========= */
        
        /**
         * returnes rendered Template for a Group Dropdown
         * @param $sel | selection
         */
        public function tplGetGroupDropdown($sel=-1) {
        	if(!isset($GLOBALS['User']['groups']) || $GLOBALS['User']['groups'] == array()) User::loadUserGroups();
        	
        	$dropdown = $this->sp->ref('UIWidgets')->getWidget('Select');

        	$dropdown->setName('eu_group');
        	
        	foreach($GLOBALS['User']['groups'] as $id=>$group){
        		if($this->checkRight('administer_group', $id) || $sel==$id) $dropdown->addOption($group->getName(), $id, $sel==$id);
        	}
        	return $dropdown->render();
        }
        
        /**
         * returnes dropdown input with All Users
         */
        public function tplGetUserDropdown($sel, $withAllUserOption=false) {
        	$user = $this->getAllUser();
        	
        	$dropdown = $this->sp->ref('UIWidgets')->getWidget('Select');

        	if($withAllUserOption) $dropdown->addOption($this->_('All User Option', 'core'), -1);
        	
        	$dropdown->setName('user_groups');
        	$dropdown->setId('user_groups');
        	
        	foreach($user as $u){
        		$dropdown->addOption($u->getNick(), $u->getId(), $sel==$u->getId());
        	}
        	
        	return $dropdown->render();
        }
    	
    	/**
         * returnes rendered Template for a Status Dropdown
         * @param $sel | selection
         */
        public function tplGetStatusDropdown($status=-1) {
        	if(!isset($GLOBALS['User']['groups']) || $GLOBALS['User']['groups'] == array())User::loadUserGroups();
        	
        	$dropdown = $this->sp->ref('UIWidgets')->getWidget('Select');

        	$dropdown->setName('eu_status');
        	
        	$dropdown->addOption($this->_('_Status: Active', 'core'), User::STATUS_ACTIVE, $status==User::STATUS_ACTIVE);
        	$dropdown->addOption($this->_('_Status: Blocked', 'core'), User::STATUS_BLOCKED, $status==User::STATUS_BLOCKED);
        	$dropdown->addOption($this->_('_Status: Deleted', 'core'), User::STATUS_DELETED, $status==User::STATUS_DELETED);
        	$dropdown->addOption($this->_('_Status: Has to activate', 'core'), User::STATUS_HAS_TO_ACTIVATE, $status==User::STATUS_HAS_TO_ACTIVATE);
        	return $dropdown->render();
        }
        
        /**
         * returnes Main template for Usercenter
         */
        public function tplUsercenter() {
        	$newPassword = $this->sp->ref('TextFunctions')->generatePassword(14, 5, 2, 2);
        	$this->debugVar($newPassword);
        	$this->debugVar($this->hashPassword($newPassword, $this->sp->ref('TextFunctions')->generatePassword(51, 13, 7, 7)));
        	if(isset($_SESSION['User']['id']) && $this->sp->ref('Rights')->c($_SESSION['User']['id'], 'User', 'usercenter')){
        		$tpl = new ViewDescriptor($this->config['tpl']['Usercenter']['main']);
        		$this->debugVar('asdf');
        		return $tpl->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
        	}
        }
        
		/* ---------  User ------- */
        /**
         * returnes Template for Userchapter in the Usercenter
         * @param $page
         */
        public function tplUCUser($page=1) {
        	if(isset($_SESSION['User']['id']) && $this->checkRight('usercenter')){
        		$tpl = new ViewDescriptor($this->config['tpl']['Usercenter']['user']);

        		$tpl->addValue('pagina_active', $page);
        		$tpl->addValue('pagina_count', ceil($this->getAllUserCount()/$this->config['user_per_page']));
        		
        		$groups = array();
        		
        		// create group filter
        		if(!isset($GLOBALS['User']['groups']) || $GLOBALS['User']['groups'] == array()) User::loadUserGroups();

        		//foreach($GLOBALS['User']['groups'] as $id=>$name) if($this->checkRight('administer_group', $id)) $groups[] = $id;
				
        		$user = $this->getAllUser($page, $groups);

        		// build view descriptor
        		foreach($user as $u){
        			$stpl = new SubViewDescriptor('user');
        			
        			$stpl->addValue('id', $u->getId());
        			$stpl->addValue('nick', $u->getNick());
        			$stpl->addValue('email', $u->getEmail());
        			$stpl->addValue('status', $u->getStatus());
        			$stpl->addValue('group', $u->getGroup());
        			$stpl->addValue('group_id', $u->getGroupId());
        			
        			if(($this->checkRight('administer_group', $u->getGroupId()) || $this->checkRight('edit_user', $u->getId()))) {
        				$sub = new SubViewDescriptor('edit');
        				$sub->addValue('id', $u->getId());
        				
        				$stpl->addSubView($sub);
        				unset($sub);
        			}
        			
        			$tpl->addSubView($stpl);
        			unset($stpl);
        		}
        		
        		return $tpl->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
        	}
        }
        public function tplUCUserNew() {
        	if($this->isAllowedToAdministerAnyGroup()){
        		$tpl = new ViewDescriptor($this->config['tpl']['Usercenter']['new_user']);
        		
        		$tpl->addValue('group', $this->tplGetGroupDropdown());
        		$tpl->addValue('status', $this->tplGetStatusDropdown());
        		
        		return $tpl->render();
        	} else  {
        		$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
        	}
        }
        
        /**
         * returnes rendered Template for the UserEdit page
         * @param $id
         */
        public function tplUCUserEdit($id) {
        	$user = $this->getUserInfo($id);
        	if($this->checkRight('edit_user', $user->getId()) || $this->checkRight('administer_group', $user->getGroupId())){
        		
        		$tpl = new ViewDescriptor($this->config['tpl']['Usercenter']['edit_user']);
        		
        		if($user->getId() != null) {
	        		$user->loadData($this->sp);
	
	        		$tpl->addValue('id', $user->getId());
	        		$tpl->addValue('nick', $user->getNick());
	        		$tpl->addValue('email', $user->getEMail());
	        		$tpl->addValue('pwd', '********');
	        		$tpl->addValue('group', $this->tplGetGroupDropdown($user->getGroupId()));
	        		$tpl->addValue('groupId', $user->getGroupId());
	        		$tpl->addValue('maxFileSize', $this->config['max_file_size']);
	        		
	        		if($this->sp->ref('Rights')->c($_SESSION['User']['id'], 'User', 'administer_group', $user->getGroup())){
	        			$s = new SubViewDescriptor('status');
	        			$s->addValue('status', $this->tplGetStatusDropdown($user->getStatus()));
	        			$tpl->addSubView($s);
	        			unset($s);
	        		}
	        		$tpl->addValue('statusId', $user->getStatus());
	        		
	        		foreach($user->getUserData() as $group){
	        			if($group->getName() == 'Userimage'){
	        				foreach($group->getData() as $data){
	        					$path = ($data->getValue() != -1) ? $this->sp->ref('Gallery')->getImagePathById($data->getValue()) : $this->sp->ref('Gallery')->getImagePathById($this->config['user_image_default_id']);
	        					
	        					$tpl->addValue('ui_path', ($path=='') ? $this->sp->ref('Gallery')->getImagePathById($this->config['user_image_default_id']) : $path);
	        				}
	        			} else {
		        			$g = new SubViewDescriptor('datagroup');
		        			
		        			$g->addValue('id', $group->getId());
		        			$g->addValue('name', $group->getName());
		        			$g->addValue('desc', $group->getDesc());
		        			
		        			foreach($group->getData() as $data){
		        				$d = new SubViewDescriptor('data');

		        				switch($data->getType()){
		        					case self::GROUP_TYPE_TEXT:
		        						$t = new SubViewDescriptor('text');
		        						break;
		        					case self::GROUP_TYPE_INT:
		        						$t = new SubViewDescriptor('int');
		        						break;
		        					case self::GROUP_TYPE_CHECKBOX:
		        						$t = new SubViewDescriptor('checkbox');
		        						$t->addValue('checked', ($t->getValue()==1) ? 'checked="checked"' : '');
		        						break;
		        					case self::GROUP_TYPE_IMAGE:
		        						$t = new SubViewDescriptor('image');
		        						
		        						$path = ($data->getValue() != -1) ? $this->sp->ref('Gallery')->getImagePathById($data->getValue()) : $this->sp->ref('Gallery')->getImagePathById($this->config['user_image_default_id']);
		        						
		        						$t->addValue('path', ($path=='') ? $this->sp->ref('Gallery')->getImagePathById($this->config['user_image_default_id']) : $path);
		        						break;
		        				}
		        				
		        				
		        				$t->addValue('id', $data->getId());
		        				$t->addValue('g_id', $group->getId());
		        				$t->addValue('name', $data->getName());
		        				$t->addValue('desc', $data->getDesc());
		        				$t->addValue('help', $data->getHelp());
		        				$t->addValue('value', $data->getValue());
		        				
		        				$d->addSubView($t);
		        				$g->addSubView($d);
		        				unset($d);
		        				unset($t);
		        			}
	        			
		        			$tpl->addSubView($g);
		        			unset($g);
	        			}
	        		}
	        		
	        		return $tpl->render();
        		} else return 'wrong_id';
        	} else {
        		$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
        	} 
        }
		/* ---------  Usergroups ------- */
        public function tplUCUsergroups($page=1) {
        	if($this->checkRight('usercenter')){
        		$groups = $this->getUsergroups($page);
        		$tpl = new ViewDescriptor($this->config['tpl']['Usercenter']['usergroup']);
        		
        		$tpl->addValue('pagina_active', $page);
        		$tpl->addValue('pagina_count', ceil($this->getAllUserGroupsCount()/$this->config['usergroup_per_page']));
        		if(!$this->checkRight('create_group')) $tpl->showSubView('no_rights_create');
        		
        		foreach($groups as $group){
        			$g = new SubViewDescriptor('group');
        			
        			$g->addValue('id', $group->getId());
        			$g->addValue('name', $group->getName());
        			$g->addValue('count', $this->getAllUserCount(-1, $group->getId()));
        			
        			if(($this->checkRight('edit_group', $group->getId()))) {
        				$sub = new SubViewDescriptor('edit');
        				$sub->addValue('id', $group->getId());
        				
        				$g->addSubView($sub);
        				unset($sub);
        			}
        			
        			$tpl->addSubView($g);
        			unset($g);
        		}
        		
        		return $tpl->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
        	}
        }
        /* -----------  Userdata ------- */
        public function tplUCUserdata($page=1){
        	if($this->checkRight('usercenter')){
        		$data = $this->getAllUserData($page);
        		$tpl = new ViewDescriptor($this->config['tpl']['Usercenter']['userdata']);
        		if(!isset($GLOBALS['User']['groups']) || $GLOBALS['User']['groups'] == array()) User::loadUserGroups();
        		
        		$tpl->addValue('pagina_active', $page);
        		$tpl->addValue('pagina_count', ceil($this->getAllUserdataCount()/$this->config['userdata_per_page']));
        		
        		foreach($GLOBALS['User']['groups'] as $gr) {
        			$g = new SubViewDescriptor('usergroup');
        			$g->addValue('name', $gr->getName());
        			$g->addValue('id', $gr->getId());
        			
        			$tpl->addSubView($g);
        			unset($g);
        		}
        		
        		foreach($data as $dg){
        			$new = true;
        			foreach($dg->getData() as $da){
	        			$d = new SubViewDescriptor('data');
	        			
	        			if($new) {
	        				$dd = new SubViewDescriptor('first_row');
	        				$dd->addValue('count', count($dg->getData()));
	        				$dd->addValue('gruppe', $dg->getName());
	        				$dd->addValue('id', $dg->getId());
	        				
	        				$d->addSubView($dd);
	        				unset($dd);
	        				$new = false;
	        			}
	        			
	        			$d->addValue('id', $da->getId());
	        			$d->addValue('name', $da->getName());
	        			$d->addValue('desc', $da->getDesc());
	        			$d->addValue('help', $da->getHelp());
	        			$d->addValue('default', $da->getValue());
	        			$d->addValue('type', $da->getType());
	        			
	        			foreach($GLOBALS['User']['groups'] as $gr) {
	        				$g = new SubViewDescriptor('ugroup');
	        				$g->addValue('selected', $da->isUsedByUserGroup($gr->getId()) ? 'ja' : 'nein');
	        				
	        				$d->addSubView($g);
	        				unset($g);
	        			}
	        			$tpl->addSubView($d);
	        			unset($d);
        			}
        		}
        		
        		
        		return $tpl->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
        	}
        }
        
        public function checkSessionExpiration() {
        	// create session creation time
			if(!isset($_SESSION['created_time'])) $_SESSION['created_time'] = time();
	
			// regenerate session id after specified time	
        	if($this->config['session']['regenerate_after'] > -1 && time() - $_SESSION['created_time'] > $this->config['session']['regenerate_after']){
        		session_regenerate_id(true);    // change session ID for the current session an invalidate old session ID
    			$_SESSION['created_time'] = time();  // update creation time
        	}
        	        	
        	if(isset($_SESSION['User']['id']) && isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $this->config['session']['idle_time']){

        		$this->logout();
        		$this->_msg($this->_('_session_expired'), Messages::ERROR);
        		$GLOBALS['session_expired'] = true;
        	}
        	
        	$_SESSION['last_activity'] = time();
        }
    }
?>