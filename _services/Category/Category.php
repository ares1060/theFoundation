<?php
	require_once 'model/CatTree.php';
	require_once 'model/CatTreeNode.php';
	require_once 'model/CatCategory.php';
	require_once 'view/CatView.php';
	/**
     * Implements a Category tree for any service
     * @author Matthias Eigner
     * @version: 1
     * @name: Category
     * 
     * @requires: Services required
     */
    class Category extends Service implements IService {
		
    	private $tree;
    	private $view;
    	
    	const STATUS_ONLINE = 0;
    	const STATUS_OFFLINE = 1;
    	
    	/**
         * protected $name;
         * protected $sp;
         * protected $config;
         * protected $config_file;
         */
         
        function __construct(){
        	$this->name = 'Category';
        	$this->config_file = $GLOBALS['config']['root'].'_services/Category/config.Category.php';
            parent::__construct();
            $this->tree = new CatTree();
            $this->view = new CatView($this->config, $this->tree);
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
        	$chapter = isset($args['chapter']) ? $args['chapter'] : '';
        	$page = isset($args['page']) ? $args['page'] : -1;
        	$id = isset($args['id']) ? $args['id'] : -1;
        	$action= isset($args['action']) ? $args['action']: '';
        	$cat = isset($args['cat']) ? $args['cat'] : -1;
        	
        	// Image vars
        	$link = isset($args['link']) ? $args['link'] : '';
        	$click = isset($args['click']) ? $args['click'] : '';
        	$reloadFunction = isset($args['reloadFunction']) ? $args['reloadFunction'] : '';
        	$useFunction = isset($args['useFunction']) ? $args['useFunction'] : '';
        	
        	switch($chapter){
        		default:
        			switch($action){
        				case 'setCategoryImage':
        					if($this->tree->setCategoryImage($cat, $id)){
        						$this->_msg($this->_('category update success'), Messages::INFO);
             					return true;
        					} else {
        						$this->_msg($this->_('category update error'), Messages::ERROR);
        						return false;
           					}
        					break;
        				// image actions
        				case 'loadProductImages':
        					return $this->sp->ref('Gallery')->addOnFolder($this->config['category_album_id'], 'category_'.$id, $page, $click, -1, -1, Gallery::ADDON_VIEW_MATRIX, $reloadFunction, $useFunction);
        					break;
        				case 'loadProductImagesUpload':
        					$folder = ($id == 'new') ? 'new' : 'category_'.$id;
        					return $this->sp->ref('Gallery')->addOnUpload($this->config['category_album_id'], $folder, $link);
        					break;
        			}
        			break;
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
        	$error = true;
        	include_once('setup/setup.php');
        	return $error;
        }
        
        /**
         * handles Post Variables in Admincenter
         */
        public function handleAdminPost($service){
        	// new action and edit action
        	if(isset($_POST['cat_action']) && $_POST['cat_action'] == 'new'){
        		$this->newCategory($_POST['cat_name'], $this->sp->ref('TextFunctions')->string2Web($_POST['cat_name']), $service);
        	} else if(isset($_POST['cat_action']) && $_POST['cat_action'] == 'edit'){
        		if(isset($_POST['cat_id']) && 
        			isset($_POST['cat_name']) &&
        			isset($_POST['cat_webname']) &&
        			isset($_POST['cat_desc']) && 
        			isset($_POST['cat_status'])) {
        		
        				$this->editCategory($_POST['cat_id'], $_POST['cat_name'], $_POST['cat_webname'], $_POST['cat_status'], $_POST['cat_desc'], $service, -1);
        		}
        	}
        	
        	// upload images to Category
            if(isset($_POST['action']) && $_POST['action'] == 'upload' &&
            	isset($_POST['album']) && 
            	isset($_POST['folder']) &&
            	isset($_POST['MAX_FILE_SIZE']) && 
            	isset($_POST['selected_type']) &&
            	isset($_POST['link'])){
            		
            	$folder = $_POST['folder'];
            	
            	if($this->checkRight('administer_category', $folder)){
            		$iId = $this->sp->ref('Gallery')->executeUploads(true);
            		if($iId != array()){
	            		$this->_msg($this->_('category update success'), Messages::INFO);
	            		header('Location: '.$_SERVER["HTTP_REFERER"].$_POST['link']);
	            		exit(0);
            		} else {
            			$this->_msg($this->_('category update error'), Messages::ERROR);
	            		header('Location: '.$_SERVER["HTTP_REFERER"].$_POST['link']);
            			exit(0);
            		}
            	} else {
            		$this->_msg($this->_('category update error'), Messages::ERROR);
	            	header('Location: '.$_SERVER["HTTP_REFERER"].$_POST['link']);
	            	exit(0);
            	}
            }
        }
        
        /**
         * creates root node for service category tree
         * @param $service
         */
        private function createServiceTree($service){
        	$this->tree->insertNode(new CatTreeNode(-1, new CatCategory(-1, $service, strtolower($service), 1), -1, -1), -1, '');
        }
        
        /**
         * deletes category and Subcategories
         * @param $id
         * @param $service
         */
        public function deleteCategory($id, $service){
        	return $this->tree->deleteSubTree($id, $service);
        }
        
        /**
         * returnes Category
         * @param $id
         */
        public function getCategory($id){
        	return $this->tree->getCategory($id);
        }
         /**
         * returnes Category
         * @param $id
         */
        public function getCategoryByName($name){
        	return $this->tree->getNodeByName($name);
        }
         /**
         * returnes Categorytree
         * @param $id
         */
        public function getServiceCategories($service){
        	return $this->tree->getNodeForService($service);
        }
        /**
         * returnes array with parents to given gategory id
         * @param unknown_type $id
         */
        public function getCategoryPath($id){
        	return $this->tree->getCategoryPath($id);
        }
        
        public function getChildrenForCategory($id, $status=-1){
        	return $this->tree->getChildrenForNodeId($id, $status);
        }
        /** ----- Admincenter ---- */
        
        public function changeCategoryOrder($service, $id, $parent=-1, $after=-1, $before=-1){
        	if($id > 0 && ($parent > 0 || $after > 0 || $before > 0)) {
        		if($this->checkRight('administer_category', $service)){
        			return $this->tree->changeCategoryOrder($service, $id, $parent, $after, $before);
        		} else {
        			$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        			return false;
        		}
        	} else {
        		return false;
        	}
        }
   		
   		/**
         * inserts category to service tree
         * @param $name
         * @param $webname
         * @param $service
         * @param $parent_id
         */
        public function newCategory($name, $webname, $service, $parent_id=-1){
        	if($this->checkRight('administer_category', $service)){
        		if($name != '' && $webname != ''){
        			if(!$this->tree->ServiceHasTree($service)) $this->createServiceTree($service);
        			
        			$webname = $this->sp->ref('TextFunctions')->string2Web($name);
        			$name = $this->sp->ref('TextFunctions')->renderUmlaute($name);
        			return $this->tree->insertNode(new CatTreeNode(-1, new CatCategory(-1, $name, $webname), -1, -1, 0), $parent_id, $service);
        		} else return false;
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }	
        
        public function editCategory($id, $name, $webname, $status, $desc, $service, $img) {
        	if($this->checkRight('administer_category', $service)){
        		if($id > 0 && $name != '' && $webname != ''){
        			$name = $this->sp->ref('TextFunctions')->renderUmlaute($name);
        			$webname = $this->sp->ref('TextFunctions')->string2Web($webname);
        			$desc = $this->sp->ref('TextFUnctions')->renderUmlaute($desc);
        			
        			return $this->tree->editCategory($id, $name, $webname, $status, $desc, $service, $img);
        		} else return false;
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
        /*** template */
        public function tplEditCategory($id, $service) {
        	if($this->checkRight('administer_category', $service)){
        		return $this->view->tplEditCategory($this->tree->getNodeById($id), $service);
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
        	}
        }
   	 	public function tplGetCategoryAdmincenter($service){
        	if($this->checkRight('administer_category', $service)){
        		return $this->view->tplAdmincenter($this->tree->getServiceCategories($service), $service);
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
        	}
        }
        
        public function tplCategoryTreeAdmin($service, $cat, $param, $style='radio'){
        	return $this->view->tplCategoryTreeAdmin($this->tree->getServiceCategories($service), $cat, $style, $service, $param);
        }
    }
?>