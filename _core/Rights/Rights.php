<?php
	
	/**
	 * This Service manages rights and helps doing authorization checks.
	 * 
	 * MODEL:
	 * The Model uses the User service's user and group tables.
	 * 
	 * prefix_rights
	 * 		name
	 * 		service
	 * 
	 * prefix_user_right
	 * 		user_id
	 * 		right_id
	 * 
	 * prefix_group_right
	 * 		group_id
	 * 		right_id
	 * 
	 * @author Immanuel
	 *
	 * @dependency: User
	 */
	class Rights extends Service implements IService  {
        
		private $rightCache = array();
		
        function __construct(){
        	$this->name='Rights';
        	$this->ini_file = $GLOBALS['to_root'].'_core/Rights/Rights.ini';
            parent::__construct();
            //$this->sp->run('Localization', array('load'=>$this->config['loc_file']));
        }

        public function getSettings() { return $this->settings; }
        
        public function view($args) {
            return '';
        }
        public function admin($args){
            return '';
        }
        public function run($args){
           return false;
        }
        
        public function data($args){
        	return '';
        }  
        
        public function setup(){
        	if(isset($GLOBALS['testDatabase']) && $GLOBALS['testDatabase']){
          		// delete old databases
        		$sql = '
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'rights`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'right_group`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'right_user`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'userdata_group`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'userdata_user`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'usergroup`;
        		';
        		$this->mysqlMultipleSetup($sql);
        	}
        	
        	$sql = '
				-- --------------------------------------------------------
				--
				-- Tabellenstruktur fuer Tabelle `'.$GLOBALS['db']['db_prefix'].'rights`
				--
        		CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'rights` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(50) NOT NULL,
				  `service` varchar(50) NOT NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `name` (`name`,`service`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
				    
				-- --------------------------------------------------------
				--
				-- Tabellenstruktur fuer Tabelle `pp_right_group`
				--    	
				CREATE TABLE IF NOT EXISTS `'.$GLOBALS['db']['db_prefix'].'right_group` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `group_id` int(11) NOT NULL,
				  `right_id` int(11) NOT NULL,
				  `param` varchar(300) CHARACTER SET utf8 NOT NULL,
				  `auth` int(1) NOT NULL DEFAULT "1",
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `relation` (`group_id`,`right_id`,`param`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

				-- --------------------------------------------------------
				--
				-- Tabellenstruktur fuer Tabelle `pp_right_user`
				--
				CREATE TABLE IF NOT EXISTS `'.$GLOBALS['db']['db_prefix'].'right_user` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `user_id` int(11) NOT NULL,
				  `right_id` int(11) NOT NULL,
				  `param` varchar(300) CHARACTER SET utf8 NOT NULL,
				  `auth` int(1) NOT NULL DEFAULT "1",
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `user_id` (`user_id`,`right_id`,`param`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
			';	

        	return $this->mysqlMultipleSetup($sql);
        }
        
        /**
         * Creates a right with the given name for the given service
         * @param $service The service's name.
         * @param $name The name of the right. If the right already exists the call is ignored.
         */
        public function addRight($service, $name){
        	$sql = 'INSERT INTO '.$GLOBALS['db']['db_prefix'].'rights (`id`, `name`, `service`) VALUES (\'\', \''.mysql_real_escape_string($name).'\', \''.mysql_real_escape_string($service).'\');';
        	mysql_query($sql);
        }
        
        /**
         * Removes the right with the given name for the given service
         * @param $service The service's name.
         * @param $name The name of the right. If the right doesn't exist the call is ignored.
         */
		public function removeRight($service, $name){
        	$sql = 'DELETE FROM '.$GLOBALS['db']['db_prefix'].'rights WHERE name = \''.mysql_real_escape_string($name).'\' AND service = \''.mysql_real_escape_string($service).'\';';
        	mysql_query($sql);
        }
        
        /**
         * Gives the user the right in the given service.
         * @param $service The service's name
         * @param $rightName The right's name
         * @param $userID The user's ID
         * @param $param An optional parameter string
         */
		public function authorizeUser($service, $rightName, $userID, $param = ''){
        	$sql = 'SELECT id FROM '.$GLOBALS['db']['db_prefix'].'rights WHERE name = \''.mysql_real_escape_string($rightName).'\' AND service = \''.mysql_real_escape_string($service).'\';';
        	$row = mysql_fetch_row(mysql_query($sql));
        	if(is_array($row)){
        	    //check if an entry for this user already exists
        		if(mysql_num_rows(mysql_query('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'right_user WHERE user_id = \''.mysql_real_escape_string($userID).'\' AND right_id = \''.mysql_real_escape_string($row[0]).'\' AND param = \''.mysql_real_escape_string($param).'\';')) > 0){
        			$sql = 'UPDATE '.$GLOBALS['db']['db_prefix'].'right_user SET auth = \'1\' WHERE user_id = \''.mysql_real_escape_string($userID).'\' AND right_id = \''.mysql_real_escape_string($row[0]).'\' AND param = \''.mysql_real_escape_string($param).'\';';
        		} else {
        			$sql = 'INSERT INTO '.$GLOBALS['db']['db_prefix'].'right_user (`user_id`, `right_id`, `param`, `auth`) VALUES (\''.mysql_real_escape_string($userID).'\', \''.mysql_real_escape_string($row[0]).'\', \''.mysql_real_escape_string($param).'\', \'1\');';
        		}
        		//$this->debugVar($sql);
        		return mysql_query($sql);
        	} return false;
        }
        
        /**
         * Gives the group the right in the given service.
         * @param $service The service's name
         * @param $rightName The right's name
         * @param $groupID The group's ID
         * @param $param An optional parameter string
         */
		public function authorizeGroup($service, $rightName, $groupID, $param = ''){
		    $sql = 'SELECT id FROM '.$GLOBALS['db']['db_prefix'].'rights WHERE name = \''.mysql_real_escape_string($rightName).'\' AND service = \''.mysql_real_escape_string($service).'\';';
        	$row = mysql_fetch_row(mysql_query($sql));
        	if(is_array($row) && $row != array()){
        		//check if an entry for this group already exists
        		if(mysql_num_rows(mysql_query('SELECT id FROM '.$GLOBALS['db']['db_prefix'].'right_group WHERE group_id = \''.mysql_real_escape_string($groupID).'\' AND right_id = \''.mysql_real_escape_string($row[0]).'\' AND param = \''.mysql_real_escape_string($param).'\';')) > 0){
        			$sql = 'UPDATE '.$GLOBALS['db']['db_prefix'].'right_group SET auth = \'1\' WHERE group_id = \''.mysql_real_escape_string($groupID).'\' AND right_id = \''.mysql_real_escape_string($row[0]).'\' AND param = \''.mysql_real_escape_string($param).'\';';
        		} else {
        			$sql = 'INSERT INTO '.$GLOBALS['db']['db_prefix'].'right_group (`id`, `group_id`, `right_id`, `param`, `auth`) VALUES (\'\', \''.mysql_real_escape_string($groupID).'\', \''.mysql_real_escape_string($row[0]).'\', \''.mysql_real_escape_string($param).'\', \'1\');';
        		}
        		mysql_query($sql);
        	}
        }

        /**
         * Takes the right from the user in the given service.
         * @param $service The service's name
         * @param $rightName The right's name
         * @param $userID The user's ID
         * @param $param An optional parameter string
         */
		public function unauthorizeUser($service, $rightName, $userID, $param = ''){
			$sql = 'SELECT id FROM '.$GLOBALS['db']['db_prefix'].'rights WHERE name = \''.mysql_real_escape_string($rightName).'\' AND service = \''.mysql_real_escape_string($service).'\';';
        	$row = mysql_fetch_row(mysql_query($sql));
        	if(is_array($row)){
        	    //check if an entry for this user already exists
        		if(mysql_num_rows(mysql_query('SELECT id FROM '.$GLOBALS['db']['db_prefix'].'right_user WHERE user_id = \''.mysql_real_escape_string($userID).'\' AND right_id = \''.mysql_real_escape_string($row[0]).'\' AND param = \''.mysql_real_escape_string($param).'\';')) > 0){
        			$sql = 'UPDATE '.$GLOBALS['db']['db_prefix'].'right_user SET auth = \'0\' WHERE user_id = \''.mysql_real_escape_string($userID).'\' AND right_id = \''.mysql_real_escape_string($row[0]).'\' AND param = \''.mysql_real_escape_string($param).'\';';
        		} else {
        			$sql = 'INSERT INTO '.$GLOBALS['db']['db_prefix'].'right_group (`id`, `group_id`, `right_id`, `param`, `auth`) VALUES (\'\', \''.mysql_real_escape_string($groupID).'\', \''.mysql_real_escape_string($row[0]).'\', \''.mysql_real_escape_string($param).'\', \'0\');';
        		}
        		mysql_query($sql);
        	}
        }
        
        /**
         * Takes the right from the group in the given service.
         * @param $service The service's name
         * @param $rightName The right's name
         * @param $groupID The group's ID
		 * @param $param An optional parameter string
         */
		public function unauthorizeGroup($service, $rightName, $groupID, $param = ''){
			$sql = 'SELECT id FROM '.$GLOBALS['db']['db_prefix'].'rights WHERE name = \''.mysql_real_escape_string($rightName).'\' AND service = \''.mysql_real_escape_string($service).'\';';
        	$row = mysql_fetch_row(mysql_query($sql));
        	if(is_array($row)){
        		//check if an entry for this group already exists
        		if(mysql_num_rows(mysql_query('SELECT id FROM '.$GLOBALS['db']['db_prefix'].'right_group WHERE group_id = \''.mysql_real_escape_string($groupID).'\' AND right_id = \''.mysql_real_escape_string($row[0]).'\' AND param = \''.mysql_real_escape_string($param).'\';')) > 0){
        			$sql = 'UPDATE '.$GLOBALS['db']['db_prefix'].'right_group SET auth = \'0\' WHERE group_id = \''.mysql_real_escape_string($groupID).'\' AND right_id = \''.mysql_real_escape_string($row[0]).'\' AND param = \''.mysql_real_escape_string($param).'\';';
        		} else {
        			$sql = 'INSERT INTO '.$GLOBALS['db']['db_prefix'].'right_group (`id`, `group_id`, `right_id`, `param`, `auth`) VALUES (\'\', \''.mysql_real_escape_string($groupID).'\', \''.mysql_real_escape_string($row[0]).'\', \''.mysql_real_escape_string($param).'\', \'0\');';
        		}
        		mysql_query($sql);
        	}
        }
        
        /**
         * Clears the authorization settings for the given user and the given parameters. Use NULL as a wildcard. $service, $rightName, $userID or $param must not be NULL at once.
         * @param $service The service's name
         * @param $rightName The right's name
         * @param $userID The user's ID
         * @param $param An optional parameter string
         */
		public function clearUserAuthorization($service, $rightName, $userID, $param = ''){
			$rightFilter = '';
			if($service != NULL && $rightName != NULL){
				$sql = 'SELECT id FROM '.$GLOBALS['db']['db_prefix'].'rights WHERE name = \''.mysql_real_escape_string($rightName).'\' AND service = \''.mysql_real_escape_string($service).'\';';
				$row = mysql_fetch_row(mysql_query($sql));
				$rightFilter = ' AND right_id =  \''.mysql_real_escape_string($row[0]).'\'';
			} else if($service != NULL && $rightName == NULL){
				$sql = 'SELECT id FROM '.$GLOBALS['db']['db_prefix'].'rights WHERE service = \''.mysql_real_escape_string($service).'\';';
				while($row = mysql_fetch_row(mysql_query($sql))){
					$rightFilter .= 'OR right_id =  \''.mysql_real_escape_string($row[0]).'\'';
				}
				$rightFilter = ' AND ('.substr($rightFilter, 2).')';
			} else if($service == NULL && $rightName != NULL){
				$sql = 'SELECT id FROM '.$GLOBALS['db']['db_prefix'].'rights WHERE name = \''.mysql_real_escape_string($rightName).'\';';
				while($row = mysql_fetch_row(mysql_query($sql))){
					$rightFilter .= 'OR right_id =  \''.mysql_real_escape_string($row[0]).'\'';
				}
				$rightFilter = ' AND ('.substr($rightFilter, 2).')';
			}
			
			if($userID != NULL){
				$userID = ' user_id = \''.mysql_real_escape_string($userID).'\' ';
			} else {
				$userID = '';
			}
			
			if($param != NULL){
				$param = ' param = \''.mysql_real_escape_string($param).'\' ';
			} else {
				$param = '';
			}
			
        	if($rightFilter != '' || $userID != '' || $param != ''){
				$sql = 'DELETE FROM '.$GLOBALS['db']['db_prefix'].'right_user WHERE '.$userID.$param.$rightFilter.';';
        		mysql_query($sql);
        	}
        }
        
        /**
         * Clears the authorization settings for the given group and the given parameters. Use NULL as a wildcard. $service, $rightName, $groupID or $param must not be NULL at once.
         * @param $service The service's name
         * @param $rightName The right's name
         * @param $groupID The group's ID
		 * @param $param An optional parameter string
         */
		public function clearGroupAuthorization($service, $rightName, $groupID, $param = ''){
					$rightFilter = '';
			if($service != NULL && $rightName != NULL){
				$sql = 'SELECT id FROM '.$GLOBALS['db']['db_prefix'].'rights WHERE name = \''.mysql_real_escape_string($rightName).'\' AND service = \''.mysql_real_escape_string($service).'\';';
				$row = mysql_fetch_row(mysql_query($sql));
				$rightFilter = ' AND right_id =  \''.mysql_real_escape_string($row[0]).'\'';
			} else if($service != NULL && $rightName == NULL){
				$sql = 'SELECT id FROM '.$GLOBALS['db']['db_prefix'].'rights WHERE service = \''.mysql_real_escape_string($service).'\';';
				while($row = mysql_fetch_row(mysql_query($sql))){
					$rightFilter .= 'OR right_id =  \''.mysql_real_escape_string($row[0]).'\'';
				}
				$rightFilter = ' AND ('.substr($rightFilter, 2).')';
			} else if($service == NULL && $rightName != NULL){
				$sql = 'SELECT id FROM '.$GLOBALS['db']['db_prefix'].'rights WHERE name = \''.mysql_real_escape_string($rightName).'\';';
				while($row = mysql_fetch_row(mysql_query($sql))){
					$rightFilter .= 'OR right_id =  \''.mysql_real_escape_string($row[0]).'\'';
				}
				$rightFilter = ' AND ('.substr($rightFilter, 2).')';
			}
			
			if($groupID != NULL){
				$groupID = ' group_id = \''.mysql_real_escape_string($userID).'\' ';
			} else {
				$groupID = '';
			}
			
			if($param != NULL){
				$param = ' param = \''.mysql_real_escape_string($param).'\' ';
			} else {
				$param = '';
			}
			
        	if($rightFilter != '' || $userID != '' || $param != ''){
				$sql = 'DELETE FROM '.$GLOBALS['db']['db_prefix'].'right_group WHERE '.$groupID.$param.$rightFilter.';';
        		mysql_query($sql);
        	}
        }
        
        /**
         * Checks if the user is authorized.
         * @param $userID The id of the user
         * @param $service The name of the service
         * @param $rightName The name of the right
         * @param $param An optional parameter string
         * @param $strict If true and $param != '' this function will return false if $service, $rightName & $param combination isn't true for the user. If false the function will also check if the user has the general right for the $service & $rightName combination.
         */
        public function checkIfAuthorized($userID, $service, $rightName, $param = '', $strict = false){
        	if(!isset($this->rightCache[$userID]) || !isset($this->rightCache[$userID][$service])) {
        		$this->getUserRights($userID, $service);
        	}
        	// general right now counts if param is not specified 
        	if($param != '' && !$strict) {
        		// 		(				param isset										and				param = true							)		or
        	 	return (isset($this->rightCache[$userID][$service][$rightName][$param]) && $this->rightCache[$userID][$service][$rightName][$param]) ||
        	 			//	(								param not set				and							general right isset 				and 			general right = true 					)
        	 			(!isset($this->rightCache[$userID][$service][$rightName][$param]) && isset($this->rightCache[$userID][$service][$rightName]['']) && $this->rightCache[$userID][$service][$rightName]['']);

        	} else return (isset($this->rightCache[$userID][$service][$rightName][$param]) && $this->rightCache[$userID][$service][$rightName][$param]);
        	
        	
        	/*if($param != '' && !$strict) $out = (isset($this->rightCache[$userID][$service][$rightName][$param]) && $this->rightCache[$userID][$service][$rightName][$param]);
        	else return (isset($this->rightCache[$userID][$service][$rightName][$param]) && $this->rightCache[$userID][$service][$rightName][$param]);
        	
			return ($out || (isset($this->rightCache[$userID][$service][$rightName]['']) && $this->rightCache[$userID][$service][$rightName]['']));*/
        }
        
        /**
         * Shorthand alias for checkIfAuthorized()
         */
        public function c($userID, $service, $rightName, $param = '', $strict = false){
        	return $this->checkIfAuthorized($userID, $service, $rightName, $param, $strict);
        }
        
        /**
         * Fetches the user's rights from the database
         * @param $userID The user's id
         * @param $service The service for which th rights shall be retrieved
         */
        public function getUserRights($userID, $service){
        	
        	if(!isset($userID) || !isset($service)) return array();
        	
        	$sql = '
				SELECT r.name, rg.auth, rg.param FROM '.$GLOBALS['db']['db_prefix'].'rights AS r 
				RIGHT OUTER JOIN '.$GLOBALS['db']['db_prefix'].'right_group AS rg ON r.id = rg.right_id
				LEFT OUTER JOIN '.$GLOBALS['db']['db_prefix'].'user AS u ON rg.group_id = u.group
				WHERE u.id = \''.mysql_real_escape_string($userID).'\' AND r.service = \''.mysql_real_escape_string($service).'\'
				
				UNION
				
				SELECT r.name, ru.auth, ru.param FROM '.$GLOBALS['db']['db_prefix'].'rights AS r 
				RIGHT OUTER JOIN '.$GLOBALS['db']['db_prefix'].'right_user AS ru ON r.id = ru.right_id
				WHERE ru.user_id = \''.mysql_real_escape_string($userID).'\' AND r.service = \''.mysql_real_escape_string($service).'\';
				
			';
        	
        	$result = mysql_query($sql);
        	$rights = array();
        	
        	if($result !== false){
	        	while($item = mysql_fetch_assoc($result)){
	        		
	        		if(!isset($rights[$item['name']])){
	        			$rights[$item['name']] = array();
	        		} 
	        		
	        		//since the user specific rights are the latter rows in the query result the user specific rights will overwrite group rights if those already exist.
	        		$rights[$item['name']][$item['param']] = ($item['auth'] == 1);
	        	}
        	}
        	
        	$this->rightCache[$userID][$service] = $rights;
        	return $rights;
        }
    }

?>