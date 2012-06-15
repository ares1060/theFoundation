<?php
    class Documentation extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         */
        function __construct(){
            parent::__construct();
            $this->loadConfig($GLOBALS['config']['root'].'_services/Documentation/config.Documentation.php');
        }
        
        /**
         *  @param $args['service'] String | Service name to be displayed
         *  @param $args['service_id'] integer | Service id to be displayed 
         */
        public function view($args) {
            return '';
        }
        
        /**
         * (non-PHPdoc)
         * @see _core/IService::admin()
         */
        public function admin($args){
        	$action = (isset($args['action'])) ? $args['action'] : '';
        	switch($action) {
        		case 'getServices':
        			break;
        		case 'createDoc':
        			$this->createDoc($args);
        			break;
        		default:
        			break;
        	}
            return '';
        }
        
        /**
         * Creates html file for spezific Service
         * @see _core/IService::run()
         */
        public function run($args){
            
        	return false;
        }
        
        /**
         * (non-PHPdoc)
         * @see _core/IService::data()
         */
        public function data($args){
            return '';
        }
        
    	public function setup(){
        	
        }
        
        /**
         * 
         * Creates Documentation into doc folder for one or more Services
         * @param $args
         */
        public function createDoc($args){
        	
        }
        
    }
?>