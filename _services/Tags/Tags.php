<?php
	require_once 'model/TagsHelper.php';
	require_once 'model/TagsTag.php';
	require_once 'view/TagsView.php';
	
	/**
     * Description
     * @author author
     * @version: version
     * @name: name
     * 
     * @requires: Services required
     */
    class Tags extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         * protected $config_file;
         */
         
    	private $dataHelper;
    	private $viewHelper;
    	
        function __construct(){
        	$this->name = 'Tags';
        	$this->config_file = $GLOBALS['config']['root'].'_services/Tags/config.Tags.php';
            parent::__construct();
            
            $this->dataHelper = new TagsHelper();
            $this->viewHelper = new TagsView($this->config, $this->dataHelper);
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::view()
         */
        public function view($args) {
        	$service = isset($args['service']) ? $args['service'] : '';
        	$param = isset($args['param']) ? $args['param'] : '';
        	$action = isset($args['action']) ? $args['action'] : '';
        	
        	switch($action){
        		case 'get_tags':
        			return $this->tplGetTags($service, $param);
        			break;
        		case 'get_tag_cloud':
        			return $this->tplGetTagCloud($service, $param);
        			break;
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
        	$id = isset($args['id']) ? $args['id'] : -1;
        	$tag = isset($args['tag']) ? $args['tag'] : -1;
        	$service = isset($args['service']) ? $args['service'] : '';
        	$param = isset($args['param']) ? $args['param'] : '';
        	$action = isset($args['action']) ? $args['action'] : '';
        	
        	switch($action){
        		case 'add_tag':
        			//return $this->dataHelper->addTag($tag, $service, $param);
        			break;
        		case 'delete_tag':
        			//return $this->dataHelper->deleteTagFromServiceById($id, $service, $param);
        			break;
        	}
        	
            return false;
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
        	return true;
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
        public function handleAdminPost(){
        	// NO Admincenter
        }
        
        /**
         * adds Tag To Service and Param
         * @param unknown_type $tag
         * @param unknown_type $service
         * @param unknown_type $param
         */
        public function tag($tag, $service, $param){
        	if($param != '' && $param != 'new') return $this->dataHelper->addTag($tag, $service, $param);
        	else return false;
        }
        
        /**
         * parses , seperated String and saves as Tags from service and param
         * tag not used any more will be deleted from tags table
         * @param $tags
         * @param $service
         * @param $param
         */
        public function saveRawTags($tags, $service, $param){
        	//$this->dataHelper->deleteServiceTags($service, $param);
        	$uTags = $this->dataHelper->getTagsByService($service, $param);
        	$ar = explode(',',$tags);
        	
        	foreach($uTags as $tag) {
        		if(!in_array($tag->getName(), $ar)) $this->dataHelper->deleteTagFromService($tag, $service, $param);
        	}
        	
        	$error = array();
        	foreach($ar as $t){
        		if(!$this->dataHelper->tagExists($t, $service, $param)) $error[] = $this->tag($t, $service, $param);
        	}
        	if(!in_array(false, $error)) {
        		$this->_msg($this->_('_tag add success'), Messages::INFO);
        		return true;
        	} else {
        		$this->_msg($this->_('_tag add error'), Messages::ERROR);
        		return false;
        	}
        }
        
        /**
         * deletes tag from service and param
         * @param $tag
         * @param $service
         * @param $param
         */
        public function deleteTag($tag, $service, $param){
        	if($param != '' && $param != 'new') return $this->dataHelper->deleteTagFromServiceByName($tag, $service, $param);
        	else return false;
        }
        
        /**
         * deletes all Tags from given Service
         * @param $service
         * @param $param
         */
        public function deleteServiceTags($service, $param){
        	return $this->dataHelper->deleteServiceTags($service, $param);
        }
        
        /* =========  Getter ====== */
        /**
         * returnes Tag by given webname
         * @param $name
         */
        public function getTagByWebname($webname) {
        	return $this->dataHelper->getTagByWebname($webname);
        }
        
        public function getParamsForTag($tag_, $service){
        	if(!get_class($tag_) == 'TagsTag') {
        		$tag = $this->dataHelper->getTag($tag_);
        		if($tag == null) $tag = $this->dataHelper->getTagByName($tag_);
        		if($tag == null) $tag = $this->dataHelper->getTagByWebname($tag_);
           	} else $tag = $tag_;
           	
           	if($tag != null){
           		return $this->dataHelper->getParamsForTag($tag, $service);
           	} else {
           		$this->_msg($this->_('_tag not found'), Messages::ERROR);
        		return array();
           	}
        }
        
        /* =========  Template function ======== */
        public function tplGetTags($service, $param, $link=''){
        	return $this->viewHelper->getTags($service, $param, $link);
        }
     	public function tplGetAdminTags($service, $param){
        	return $this->viewHelper->getAdminTags($service, $param);
        }
     	public function tplGetTagCloud($service, $param){
        	return $this->viewHelper->getTagCloud($service, $param);
        }
    }
?>