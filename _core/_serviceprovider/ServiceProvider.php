<?php
    require_once($GLOBALS['config']['root'].'_core/Settings/Settings.php');
	require_once($GLOBALS['config']['root'].'_core/Messages/Messages.php');
    require_once($GLOBALS['config']['root'].'_core/Database/Database.php');
    require_once($GLOBALS['config']['root'].'_core/Template/Template.php');
    require_once($GLOBALS['config']['root'].'_core/Localization/Localization.php');
    require_once($GLOBALS['config']['root'].'_core/Rights/Rights.php');
    require_once($GLOBALS['config']['root'].'_core/User/User.php');
    
    class ServiceProvider {
        /**
         * @var Database
         */
    	public $db;
    
		/**
		* @var Localization
		*/
        public $loc;
        
		/**
		 * @var Messages
		 */
		public $msg;
		
		/**
		 * @var User
		 */
		public $user;
		
		/**
		 * @var Template
		 */
		public $tpl;

		/**
		* @var Rights
		*/
		public $rights;
		
		/**
		 * @var unknown_type
		 */
		public $settings;
		
		/**
		 * @var array
		 */
        private $services;

        const VERSION = '0.03A R7';
        
        public function __construct() {
        	
        	$GLOBALS['ServiceProvider'] = $this;
			$this->services = array();
			$this->settings = new Settings();
			$this->services['settings'] =& $this->settings;
			$this->loc = new Localization();
			$this->services['localization'] =& $this->loc;
            $this->db = new Database();
			$this->services['database'] =& $this->db;
            $this->msg = new Messages();
			$this->services['messages'] =& $this->msg;
            $this->tpl = new Template();
			$this->services['template'] =& $this->tpl;
            $this->user = new User();
            $this->services['user'] =& $this->user;
            $this->rights = new Rights();
            $this->services['rights'] =& $this->rights;
            $this->templates = array();
            $this->msg->run(array('message'=>$this->data('Localization', array('str'=>'INIT_COMPLETED', 'service'=>'core')), 'type'=>Messages::RUNTIME));
            $this->msg->run(array('message'=>'history_prev = '.((isset($_SESSION['history'])) ? $_SESSION['history']['prev_page'] : ''), 'type'=>Messages::RUNTIME));
            $this->msg->run(array('message'=>'history_act = '.((isset($_SESSION['history'])) ? $_SESSION['history']['active_page'] : ''), 'type'=>Messages::RUNTIME));
            
            $this->loc->loadPreloadedFiles();
            
            // check if installation is valid 
            if((!isset($GLOBALS['setup']) || !$GLOBALS['setup']) && $this->db->data(array('query'=>'SHOW TABLES like "'.$GLOBALS['db']['db_prefix'].'rights"', 'type'=>'row')) === false) {
            	// goto setup
            	header('Location: '.$GLOBALS['abs_root'].'_admincenter/setup/');
            }       
        }
        
		/**
		 * Returns a reference to the class of the requested Service.
		 * @param $name The name of the Service.
		 * @return IService
		 */
    	public function ref($name){
    		
    		if(!isset($this->services[strtolower($name)])){
				if(is_file($GLOBALS['config']['root'].'_services/'.$name.'/'.$name.'.php')){
					
					require_once($GLOBALS['config']['root'].'_services/'.$name.'/'.$name.'.php');
					if(class_exists($name)){
						$class = new $name();
						$this->services[strtolower($name)] = $class;
					} else {
						$this->__(str_replace('{@pp:service}', $name, $this->_('SERVICE_NOT_FOUND')));
						$class = null;
					}
				} else if(isset($this->services['Localization'])){
					$this->_msg(str_replace('{@pp:service}', $name, $this->_('SERVICE_NOT_FOUND')));
					$class = null;
				} else {
					$class = null;
				}
				$this->rights->getUserRights(@$_SESSION['User']['id'], $name);
			} else {
				$class = $this->services[strtolower($name)];
			}
			return $class;
		}
		
        /**
         * $name ... name des Services
         * $method ... (run, admin, view, data)
         * $args ... $args
         */
        public function exe($name, $method, $args) {
        	$class = $this->ref($name);
			if(!is_null($class)){
				if($method=='run') return $class->run($args);
				if($method=='admin') return $class->admin($args);
				if($method=='view') return $class->view($args);
				if($method=='data') return $class->data($args);
				if($method=='ref') return $class;
			}
			return '';
        }
        
        // ----------------------------     Services wrapper
        public function run($name, $args) {
        	$ref = $this->ref($name);
        	return ($ref != null) ? $ref->run($args) : '';
       	}
        public function admin($name, $args){
        	$ref = $this->ref($name);
        	return ($ref != null) ? $ref->admin($args) : '';
       	}
        public function view($name, $args){
        	$ref = $this->ref($name);
        	return ($ref != null) ? $ref->view($args) : '';
       	}
        public function data($name, $args){
        	$ref = $this->ref($name);
        	return ($ref != null) ? $ref->data($args) : '';
       	}

        private function _($str, $service='core'){ return $this->data('Localization', array('str'=>$str, 'service'=>$service));}
        private function __($str, $type=Messages::DEBUG_ERROR, $service='core'){ $this->msg->run(array('message'=>$str, 'type'=>$type));}
        
        /**
         * Setup function for creating necessary tables, folders and files
         */
       	public function setup(){
       		return true;
       	}
        
    }
?>