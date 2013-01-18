<?php
	/**
     * Description
     * @author author
     * @version: version
     * @name: name
     * 
     * @requires: Services required
     */
    class ServiceName extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         * protected $config_file;
         */
         
        function __construct(){
        	$this->name = 'ServiceName';
        	$this->config_file = ''; // $GLOBALS['config']['root'].'_services/Gallery/config.Gallery.php';
 			$this->ini_file = '';//$GLOBALS['to_root'].'_services/Admincenter/Admincenter.ini';           
 			parent::__construct();
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
            return '';
        }
        
        /**
         * Function for Service Setup
         * @see _core/_model/IService::setup()
         */
        public function setup(){
        	
        }
        
        /**
         * handles Post Variables in Admincenter
         */
        public function handleAdminPost(){
        	
        }
    }
?>