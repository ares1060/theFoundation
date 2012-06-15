<?php
	/**
     * Implements a Newsletter
     * @author Matthias
     * @version: 0.1
     * @name: Newsletter
     * 
     * @requires: Services required
     */
    class Newsletter extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         */
    	protected $name = 'Newsletter';
         
        function __construct(){
            parent::__construct();
            $this->loadConfig($GLOBALS['config']['root'].'_services/Newsletter/config.Newsletter.php');
            if(isset($this->config['loc_file'])) $this->sp->run('Localization', array('load'=>$this->config['loc_file']));
       
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::view()
         */
        public function view($args) {
        	$kat = isset($args['kat']) ? $args['kat'] : 'default';
        	$action = isset($args['action']) ? $args['action'] : 'register';
        	if($action != ''){
        		switch($action){
        			case 'register':
        				return $this->getRegisterTpl($kat);
        				break;
        			case 'unregister':
        				return $this->getUnregisterTpl();
        				break;
        		}
        	}
            return '';
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::admin()
         */
        public function admin($args){
            return '';
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::run()
         */
        public function run($args){
            return false;
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::data()
         */
        public function data($args){
            return '';
        }
        
        /**
         * Function for Service Setup
         * @see _core/_model/IService::setup()
         */
        public function setup(){
        	$query = '-- --------------------------------------------------------
			--
			-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'newsletter`
			--
			
			CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'newsletter` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(200) NOT NULL,
			  `email` varchar(100) NOT NULL,
			  `date` datetime NOT NULL,
			  `ip` varchar(15) NOT NULL,
			  `unregister` varchar(200) NOT NULL,
			  `kat` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
			
			-- --------------------------------------------------------
			--
			-- Tabellenstruktur fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'newsletter_kat`
			--
			
			CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'newsletter_kat` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(50) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;
			
			--
			-- Daten fŸr Tabelle `'.$GLOBALS['db']['db_prefix'].'newsletter_kat`
			--
			
			INSERT INTO `'.$GLOBALS['db']['db_prefix'].'newsletter_kat` VALUES(1, \'default\');';

        	return $this->mysqlSetup($query);
        	//move Localization
        }
        
        /**
         * 
         * registers an email to the Newsletter 
         * @param string $email
         * @param int $kat
         */
        public function register($email, $kat, $name){
        	if($email != '' && $name != ''){
	        	if(!$this->isRegistered($email, $kat)){
	        		
	        		if($kat == 0) $kat = $this->getKatId($kat);
	        		
	        		if($kat != -1) {
	        			$unregister_code = md5($email).md5($name.$this->config['unregister_salt']);
	        			$query = 'INSERT INTO '.$GLOBALS['db']['db_prefix'].'newsletter 
	        									(`email`, `name`, `date`, `ip`, `unregister`, `kat`)
	        							VALUES ("'.mysql_real_escape_string($email).'",
	        									"'.mysql_real_escape_string($name).'", 
	        									NOW(), 
	        									"'.$this->sp->ref('TextFunctions')->getIp().'", 
	        									"'.$unregister_code.'", 
	        									"'.$kat.'")';
	        			
	        			if($this->mysqlBool($query)) {
	        				$this->__($this->_('REGISTER_SUCCESS', 'newsletter'), Messages::INFO);
	        				return true;
	        			} else {
	        				$this->__(str_replace('{@pp:service}', $this->name, $this->_('INTERNAL_ERROR', 'core')), Messages::ERROR);
	        				return false;
	        			}
	        			
	        		} else {
	        			$this->__($this->_('KAT_DOES_NOT_EXISTS', 'newsletter'), Messages::ERROR);
	        			return false;
	        		}
	        	} else {
	        		$this->__($this->_('ALLREADY_REGISTERED', 'newsletter'), Messages::ERROR);
	        		return false;
	        	}
        	} else {
        		$this->__($this->_('NEED_NAME_AND_EMAIL', 'newsletter'), Messages::ERROR);
	        	return false;
        	}
        }
        
        /**
         * 
         * Unregisters an email from the Newsletter
         * @param $unregister_code
         */
        public function unregister($unregister_code){
        	$r = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'newsletter WHERE `unregister`="'.mysql_real_escape_string($unregister_code).'";');
        	
        	if(is_array($r) && $r != array()){
        		$query = 'DELETE FROM '.$GLOBALS['db']['db_prefix'].'newsletter WHERE `id`="'.$r['id'].'";';
        		if($this->mysqlBool($query)) {
        			$this->__($this->_('UNREGISTER_SUCCESS', 'newsletter'), Messages::INFO);
        			return true;
        		} else {
        			$this->__(str_replace('{@pp:service}', $this->name, $this->_('INTERNAL_ERROR', 'core')), Messages::ERROR);
        			return false;
        		} 
        	} else {
        		$this->__($this->_('WRONG_UNREGISTER_CODE', 'newsletter'), Messages::ERROR);
        		return false;
        	}
        }
        
        /**
         * 
         * checks if an email is already registered
         * @param unknown_type $email
         */
        public function isRegistered($email, $kat){
        	$r = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'newsletter WHERE `email`="'.mysql_real_escape_string($email).'" AND `kat`="'.mysql_real_escape_string($kat).'";');
        	return is_array($r) && $r != array();
        }
        
        /**
         * 
         * returnes KategoryId from name
         * @param unknown_type $kat
         */
        public function getKatId($kat){
        	$r = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'newsletter_kat WHERE `name`="'.mysql_real_escape_string($kat).'"');
        	return (is_array($r) && $r != array()) ? $r['id'] : -1;
        }
        
        /**
         * 
         * Sends Newsletter to all email of a specific kategory
         * @param $kat
         * @param $text
         */
        public function sendNewsletter($kat, $text){
        	
        	$success = array();
        	
        	$headers = 'From: newletter@'.$this->config['domain'] . "\r\n" .
    					'Reply-To: '.$this->config['replyTo'] . "\r\n" .
    					'X-Mailer: PHP/' . phpversion().
    					'MIME-Version: 1.0' . "\r\n".
						'Content-type: text/html; charset=iso-8859-1' . "\r\n";;
        			
        	
        	if($kat == 0) $kat = $this->getKatId($kat);
        	
        	$array = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'newsletter WHERE `kat`="'.mysql_real_escape_string($kat).'"');

          	if(is_array($array)){
        		foreach($array as $email){
        			$tpl = new ViewDescriptor($this->config['tpl']['mail']);
        			
        			$tpl->addValue('text', str_replace('{@pp:name}', $email['name'], $text));
        			$tpl->addValue('name', $email['name']);
        			$tpl->addValue('unregister', $email['unregister']);
        			
        			$success[$email['email']] = mail($email['email'], 'Newsletter from '.$this->config['domain'], $tpl->render(), $headers.'To: '.$email['name'].' <'.$email['email'].'>');
        		}
        	}
        	
        	$s = '';
        	$s_=0;$e_=0;
        	foreach($success as $k=>$v){
        		$s .= ($v) ? $k.': success<br />' : $k.': error<br />';
        		if($v) $s_ ++;
        		else $e_ ++;
        	}
        	
        	if($this->config['showMessageAfterSending']){
				if($s_ > 0) $this->__($s_.' mails sent successful', Messages::INFO);
				if($e_ > 0) $this->__($e_.' could not be sent', Messages::ERROR);
        	}
        }
        
        //--render Templates
        
        /**
         * 
         * returnes the register Template
         */
        public function getRegisterTpl($kat='default') {
        	$tpl = new ViewDescriptor($this->config['tpl']['register']);
        	return $tpl->render();
        }
        
        /**
         * 
         * returnes the unregister Template
         */
        public function getUnregisterTpl() {
        	$tpl = new ViewDescriptor($this->config['tpl']['unregister']);
        	return $tpl->render();
        }
        
        // ---  POST 
        /**
         * 
         * handles POST variables and starts the native functions
         */
        public function handlePost() {
        	if(isset($_POST['nl_email']) && isset($_POST['nl_kat']) && isset($_POST['nl_name'])) $this->register($_POST['nl_email'], $_POST['nl_kat'], $_POST['nl_name']);
        	if(isset($_POST['nl_unregister'])) $this->unregister($_POST['nl_unregister']);
        }
    }
?>