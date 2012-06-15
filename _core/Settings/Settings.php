<?php
	require_once('SettingFile.php');
	require_once('SettingGroup.php');
	require_once('SettingValue.php');	
	
	/**
     * handling, Caching and loading of Setting files
     * @author author Matthias (scrapy1060@gmail.com
     * @version: version 0.1
     * @name: Settings
     */
    class Settings extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         * protected $config_file;
         */
    	
    	private $setting_files;
    	
        function __construct(){
        	$this->name = 'Settings';
            parent::__construct();
            $this->setting_files = array();
           // if(isset($this->config['loc_file'])) $this->sp->run('Localization', array('load'=>$this->config['loc_file'])); -> will be executed by Service::__construct()
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::view()
         */
        public function view($args) {
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
        	$action = isset($args['action']) ? $args['action'] : ''; 
        	$service = isset($args['service']) ? $args['service'] : ''; 
        	
        	switch($action){
        		case 'edit':
        			if($service != ''){
        				if(!isset($this->setting_files[$service])) $this->sp->ref($service);
        				if(isset($this->setting_files[$service])){
        					return $this->setting_files[$service]->updateSettings($args['groups']);
        				} else return ''; // service has no setting file
        			} else return '';
        			break;
        		default:
        			return 'no';
        			break;
        	}
            return '';
        }
        
        /**
         * Function for Service Setup
         * @see _core/_model/IService::setup()
         */
        public function setup(){
        	$this->sp->ref('Rights')->addRight('Settings', 'administer_settings');
			$this->sp->ref('Rights')->authorizeGroup('Settings', 'administer_settings', User::getUserGroup('root'));
			$this->sp->ref('Rights')->authorizeGroup('Settings', 'administer_settings', User::getUserGroup('admin'));
			
			$this->sp->ref('Rights')->addRight('Settings', 'administer_hidden_settings');
			$this->sp->ref('Rights')->authorizeGroup('Settings', 'administer_hidden_settings', User::getUserGroup('root'));
        }
        
        /**
         * handles Post Variables in Admincenter
         */
        public function handleAdminPost(){
        	
        }
        
        public function loadSettingFile($file, $service, $cache=true){
        	if(!isset($this->setting_files[$service]) || !$cache) $this->setting_files[$service] = new SettingFile($file, $service);

        	return $this->setting_files[$service];
        }
        
        public function isAllowedToEditSettings($service) {
        	return $this->checkRight('administer_settings', $service);
        }
        
        public function isAllowedToEditHiddenSettings($service){
        	return $this->checkRight('administer_hidden_settings', $service);
        }
    }
?>