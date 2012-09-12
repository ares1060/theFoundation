<?php
	require_once 'model/GalleryFolder.php';
	require_once 'model/GalleryImage.php';
	require_once 'model/GalleryDataHelper.php';
	require_once 'view/GalleryAdminView.php';
	/**
     * Description
     * @author MisterE
     * @version: 0.1
     * @name: Gallery2
     * 
     * @requires: Services required
     */
    class Gallery extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         * protected $config_file;
         */
    	
    	private $dataHelper;
    	private $frontView;
    	private $adminView;
         
        function __construct(){
        	$this->name = 'Gallery';
        	$this->ini_file = $GLOBALS['to_root'].'_services/Gallery/Gallery.ini';
            parent::__construct();
           // if(isset($this->config['loc_file'])) $this->sp->run('Localization', array('load'=>$this->config['loc_file'])); -> will be executed by Service::__construct()
           $this->dataHelper = new GalleryDataHelper($this->settings);
           $this->adminView = new GalleryAdminView($this->settings, $this->dataHelper);
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
        	$chapter = isset($args['chapter']) ? $args['chapter'] : '';
        	$action = isset($args['action']) ? $args['action'] : '';
            $ajax = isset($args['ajax']) ? $args['ajax'] : false;
           	$page = isset($args['page']) ? $args['page'] : -1;
           	
           	switch($chapter){
           		case 'album':
           			break;
           		case 'folder':
           			break;
           		default:
           			switch($action) {
           				case 'upload':
           					break;
           				case 'edit':
           					break;
           				case 'delete':
           					break;
           				default:
           					return $this->adminView->tplAdmincenter();
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