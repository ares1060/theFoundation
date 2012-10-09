<?php
	require_once 'model/GalleryFolder.php';
	require_once 'model/GalleryImage.php';
	require_once 'control/GalleryDataHelper.php';
	require_once 'control/exifReader.inc';
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
			$GLOBALS['extra_css'][] = 'services/gallery_admin.css'; 
        	
			$chapter = isset($args['chapter']) ? $args['chapter'] : '';
        	$action = isset($args['action']) ? $args['action'] : '';
            $ajax = isset($args['ajax']) ? $args['ajax'] : false;
           	$page = isset($args['page']) ? $args['page'] : -1;
           	$id = isset($args['id']) ? $args['id'] : -1;
           	
           	switch($chapter){
           		case 'folder':
           			return $this->adminView->tplFolder($id, $page);
           			break;
        		case 'settings':
        			return $this->tplSettings();
        			break;
        		case 'image':
        			if(isset($args['folder']) && $args['folder'] != ''){
        				return $this->adminView->tplImage($id, $args['folder']);
        			} else return false;
        			break;
           		default:
           			switch($action) {
           				case 'upload':
           					return $this->adminView->tplUpload($id);
           					break;
           				case 'edit':
           					return 'edit';
           					break;
           				case 'new_album':
           					if(isset($args['name']) && $args['name'] != ''){
           						$name = $args['name']; 
           						$status = (isset($args['status']) && GalleryDataHelper::validStatus($args['status'])) ? $args['status'] : GalleryDataHelper::STATUS_ONLINE; 
           						return $this->dataHelper->createAlbum($name, $status);
           					} else return false;
           					break;
           				case 'new_folder':
           					if(isset($args['name']) && $args['name'] != '' &&
           						isset($args['parent']) && $args['parent'] != ''){
           						
           							$name = $args['name'];
	           						$parent = $args['parent'];
	           						$status = (isset($args['status']) && GalleryDataHelper::validStatus($args['status'])) ? $args['status'] : GalleryDataHelper::STATUS_ONLINE;
	           						return $this->dataHelper->createFolder($parent, $name, $status);
           					} else return false;
           					break;
           				case 'delete_folder':
           					if($id > -1){
           						return $this->dataHelper->deleteFolder($id);
           					} else return false;
           					break;
           				case 'show_folder':
           					if($id > -1){
           						return $this->dataHelper->editFolder($id, '', GalleryDataHelper::STATUS_ONLINE);
           					} else return false;
           					break;
           				case 'hide_folder':
           					if($id > -1){
           						return $this->dataHelper->editFolder($id, '', GalleryDataHelper::STATUS_HIDDEN);
           					} else return false;
           					break;
           				case 'rename_folder':
           					if(isset($args['name']) && $args['name'] != '' && $id > -1) {
           						$name = $args['name'];
           						return $this->dataHelper->editFolder($id, $name, -1);
           					} else return false;
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
        	if($_POST != array() && isset($_POST['action'])){
        		switch($_POST['action'])  {				// upload
        			case 'upload':
	        			if($_POST['upload_folder'] != -1){
	        				$this->dataHelper->uploadImages($this->sp->ref('UIWidgets')->getUploads(), $_POST['upload_folder']);
	        			}
	        			break;
        			default:
        				$this->_msg($this->_('_Wrong parameters'), Messages::ERROR);
        				break;
        		}
        	}
        }
    }
?>