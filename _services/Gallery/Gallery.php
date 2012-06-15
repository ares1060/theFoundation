<?php
	require_once($GLOBALS['config']['root'].'_services/Gallery/model/GalleryMeta.php');
	require_once($GLOBALS['config']['root'].'_services/Gallery/model/GalleryAlbum.php');
	require_once($GLOBALS['config']['root'].'_services/Gallery/model/GalleryFolder.php');
	require_once($GLOBALS['config']['root'].'_services/Gallery/model/GalleryImage.php');
	require_once($GLOBALS['config']['root'].'_services/Gallery/model/exifReader.inc');
	
	require_once($GLOBALS['config']['root'].'_services/Gallery/view/GalleryFrontView.php');
	require_once($GLOBALS['config']['root'].'_services/Gallery/model/GalleryDataHandler.php');
	/**
     * Gallery
     * @author Matthias Eigner
     * @version: 0.1 R40
     * @name: Gallery
     * 
     * @requires: Services required
     */
    class Gallery extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         */
    	protected $name = 'Gallery';
    	
    	private $dataHandler;
    	private $frontView;
    	
    	const STATUS_ONLINE = 0;
    	const STATUS_OFFLINE = 1;
    	const STATUS_SERVICE_ALBUM = 2; // used for profile Pictures etc
    	
    	const IMG_STATUS_ONLINE = 0;
    	const IMG_STATUS_OFFLINE = 1;
    	const IMG_STATUS_MAIN_IMAGE = 2;
    	
    	const META_TYPE_BOOLEAN = 0;
    	const META_TYPE_INTEGER = 1;
    	const META_TYPE_STRING = 2;
    	const META_TYPE_TEXT = 3;
    	const META_TYPE_READONLY = 4;
    	
    	const SORT_NAME = 1;
    	const SORT_DATE = 2;
    	
    	const SORT_ASC = 1;
    	const SORT_DESC = 2;
    	
    	const ADDON_VIEW_MATRIX = 0;
         
        function __construct(){
        	$this->name = 'Gallery';
        	$this->config_file = $GLOBALS['config']['root'].'_services/Gallery/config.Gallery.php';
            parent::__construct();
            $this->sp->run('Localization', array('load'=>$this->config['loc_file']));
            
            $this->dataHandler = new GalleryDataHandler($this->config);
            $this->frontView = new GalleryFrontView($this->config, $this->dataHandler);
        }
        
        /* --------   Service Functions ------ */
        
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::view()
         */
        public function view($args) {
            /**
           	 * old actions handling (will be handled by gallery.php) 
           	 */
        	$action = isset($args['action']) ? $args['action'] : '';
            $page = isset($args['page']) ? $args['page'] : 1;
            $id = isset($args['id']) ? $args['id'] : -1;
           	$album = isset($args['album']) ? $args['album'] : -1;
           	$click = isset($args['clickAction']) ? str_replace(array('\\'), array(''), $args['clickAction']) : '';

           	$sort = isset($args['sort']) ? $args['sort'] : -1;
           	$sortDA = isset($args['sortDA']) ? $args['sortDA'] : -1;
           	
           	$folder = isset($args['folder']) ? $args['folder'] : -1;

           	
           	switch($action){
            	case 'image':
            		return $this->tplGetImage($id, $album, $folder, $sort, $sortDA);
            		break;
            	case 'folder':
            		return $this->tplGetFolder($id, $page, $sort, $sortDA);
            		break;
            	case 'album':
            		return $this->tplGetAlbum($id, $page, $sort, $sortDA);
            		break;
            	case 'albums':
            		return $this->tplGetAlbums();
            		break;
            	case 'smallAlbum':
            		return $this->tplSmallAlbum($album, $page, $sort, $sortDA, $click);
            		break;
            	case 'smallFolder':
            		return $this->tplSmallFolder($album, $folder, $page, $sort, $sortDA, $click);
            		break;
            /*	default:
            		return '';
            		break;*/
            }
            
            /**
           	 * new actions handling (will be handled by GalleryFrontView.php) 
           	 */
            $action = isset($args['action']) ? $args['action'] : '';
            $page = isset($args['page']) ? $args['page'] : 1;
            $id = isset($args['id']) ? $args['id'] : -1;
           	$album = isset($args['album']) ? $args['album'] : -1;
           	$folder = isset($args['folder']) ? $args['folder'] : -1;
           	
           	$sort = isset($args['sort']) ? $args['sort'] : -1;
           	$sortDA = isset($args['sortDA']) ? $args['sortDA'] : -1;
           	
           	$type = isset($args['type']) ? $args['type'] : '';
           	$perPage = isset($args['perPage']) ? $args['perPage'] : '';
           	$justOnePage = isset($args['justOnePage']) ? $args['justOnePage'] : true;

           	switch($action){
            	case 'tplSmallFolder':
            		return $this->frontView->tplSmallFolder($album, $folder, $page, $sort, $sortDA, $type, $perPage, $justOnePage);
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
        	/* --- wrap $args ---- */
        	$action = isset($args['action']) ? $args['action'] : 'admin_center';
            $ajax = isset($args['ajax']) ? $args['ajax'] : false;
           	$page = isset($args['page']) ? $args['page'] : -1;
            $rows = isset($args['rows']) ? $args['rows'] : -1;
           	$album = isset($args['album']) ? $args['album'] : -1;
           	$sort = isset($args['sort']) ? $args['sort'] : -1;
           	$sortDA = isset($args['sortDA']) ? $args['sortDA'] : -1;
           	$folder = (isset($args['folder']) && $args['folder'] != 'undefined') ? $args['folder'] : -1;
           	$link = isset($args['link']) ? $args['link'] : '';
            $id = isset($args['id']) ? $args['id'] : '';
            $special = isset($args['special']) ? $args['special'] : '';
            
            
            switch($action){
            	case 'upload':
            		return $this->tplUploadForm($link, $album, $ajax);
            		break;
            	case 'new_album':
            		return $this->tplNewAlbum($link, $ajax);
            		break;
            	case 'new_folder':
            		return $this->tplNewFolder($link, $album, $ajax);
            		break;
            	case 'list':
            		//TODO: list
            		break;
            	case 'admin_center':
            		return $this->tplAdmin($link);
            		break;
            	case 'get_album':
            		return $this->adminGetAlbum($id, $page, $rows, $sort, $sortDA, $ajax, $link);
            		break;
            	case 'get_image':
            		return $this->adminGetImage($id, $album, $folder, $sort, $sortDA, $ajax, $link);
            		break;
            	case 'get_folder':
            		return $this->adminGetFolder($id, $page, $rows, $sort, $sortDA, $ajax, $link);
            		break;
            	case 'edit_album':
            		return $this->tplEditAlbum($id, $link, $ajax);
            		break;
            	case 'edit_folder':
            		return $this->tplEditFolder($id, $link, $ajax);
            		break;
            	case 'edit_image':
            		return $this->tplEditImage($id, $link, $ajax);
            		break;
            	case 'get_folderselect':
            		return $this->getFolderSelectByAlbum($id);
            		break;
            	case 'get_sidebar_albums':
            		return $this->tplSidebarAlbums($page, $special, $id);
            		break;
            	case 'loadWysiwygUpload':
            		return $this->addOnWysiwygUpload($album, $folder, $link);
            		break;
            	default:
            		$this->_msg(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
            		/*if($ajax) return str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core'));
            		else */
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
        	$action = isset($args['action']) ? $args['action'] : '';
            $ajax = (isset($args['ajax']) && $args['ajax'] == 'true') ? true : false;
        	$id = isset($args['id']) ? $args['id'] : '';
            $thumb = isset($args['thumb']) ? $args['thumb'] : -1;
        	
           	switch($action){
            	case 'delete_image':
            		return $this->deleteImageById($id);
            		break;
            	case 'delete_album':
            		if($ajax) echo ($this->deleteAlbum($id)) ? 'true' : 'false';
            		else return $this->deleteAlbum($id);
            		break;
            	case 'delete_folder':
            		if($ajax) echo ($this->deleteFolder($id)) ? 'true' : 'false';
            		else return $this->deleteFolder($id);
            		break;
            	case 'set_folder_thumb':
            		return $this->setFolderThumb($id, $thumb);
            		break;
            	case 'set_album_thumb':
            		if($ajax) echo ($this->setAlbumThumb($id, $thumb)) ? 'true' : 'false';
            		else return $this->setAlbumThumb($id, $thumb);
            		break;
            	case 'hide_image':
            		return $this->setImageStatus($id, self::STATUS_OFFLINE);
            		break;
            	case 'show_image':
            		return $this->setImageStatus($id, self::STATUS_ONLINE);
            		break;
            	default:
            		if($ajax) return false;
            		else $this->__(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
               		break;
            		
            }
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
        
    	public function setup(){
    		if(isset($GLOBALS['testDatabase']) && $GLOBALS['testDatabase']){
          		// delete old databases
        		$sql = '
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'gallery_album`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'gallery_album_images`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'gallery_folder`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'gallery_folder_images`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'gallery_images`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'gallery_meta`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'gallery_meta_groups`;
        			DROP TABLE IF EXISTS `'.$GLOBALS['db']['db_prefix'].'gallery_meta_images`;
        			
        		';
        		$this->mysqlMultipleSetup($sql);
        	}
    		$sql = '-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `pp_gallery_album`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'gallery_album` (
					  `a_id` int(11) NOT NULL AUTO_INCREMENT,
					  `name_de` varchar(100) NOT NULL,
					  `name_en` varchar(100) NOT NULL,
					  `desc_de` text NOT NULL,
					  `desc_en` text NOT NULL,
					  `u_id` int(11) NOT NULL,
					  `c_date` datetime NOT NULL COMMENT \'creation date\',
					  `status` int(11) NOT NULL,
					  `thumb` int(11) NOT NULL,
					  PRIMARY KEY (`a_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
					
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `pp_gallery_album_images`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'gallery_album_images` (
					  `i_id` int(11) NOT NULL,
					  `a_id` int(11) NOT NULL
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `pp_gallery_folder`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'gallery_folder` (
					  `f_id` int(11) NOT NULL AUTO_INCREMENT,
					  `a_id` int(11) NOT NULL,
					  `name` varchar(100) NOT NULL,
					  `datum` datetime NOT NULL,
					  `u_id` int(11) NOT NULL,
					  `status` int(11) NOT NULL,
					  `desc` text NOT NULL,
					  `thumb` int(11) NOT NULL,
					  `sort` int(11) NOT NULL,
					  `sortDA` int(11) NOT NULL,
					  PRIMARY KEY (`f_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `pp_gallery_folder_images`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'gallery_folder_images` (
					  `f_id` int(11) NOT NULL,
					  `i_id` int(11) NOT NULL,
					  UNIQUE KEY `f_id` (`f_id`,`i_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `pp_gallery_images`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'gallery_images` (
					  `i_id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(100) NOT NULL,
					  `path` varchar(100) NOT NULL,
					  `hash` varchar(54) NOT NULL,
					  `status` int(11) NOT NULL,
					  `u_id` int(11) NOT NULL,
					  `u_date` datetime NOT NULL COMMENT \'upload date\',
					  `shot_date` datetime DEFAULT NULL,
					  PRIMARY KEY (`i_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `pp_gallery_meta`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'gallery_meta` (
					  `m_id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(100) NOT NULL,
					  `desc` varchar(100) NOT NULL,
					  `group` int(11) NOT NULL,
					  `type` int(11) NOT NULL,
					  `order` int(11) NOT NULL,
					  `readonly` int(11) NOT NULL,
					  PRIMARY KEY (`m_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;
					
					--
					-- Daten fŸr Tabelle `pp_gallery_meta`
					--
					
					INSERT INTO `pp_gallery_meta` VALUES(1, \'visible\', \'Show in Shop\', 1, 0, 0, 0);
					INSERT INTO `pp_gallery_meta` VALUES(3, \'price\', \'Price\', 1, 1, 1, 0);
					INSERT INTO `pp_gallery_meta` VALUES(4, \'desc\', \'Description\', 2, 3, 0, 0);
					INSERT INTO `pp_gallery_meta` VALUES(5, \'model\', \'Camera model\', 3, 2, 0, 1);
					INSERT INTO `pp_gallery_meta` VALUES(7, \'flash\', \'Flash Used\', 3, 1, 2, 1);
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `pp_gallery_meta_groups`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'gallery_meta_groups` (
					  `mg_id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(100) NOT NULL,
					  PRIMARY KEY (`mg_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
					
					--
					-- Daten fŸr Tabelle `pp_gallery_meta_groups`
					--
					
					INSERT INTO `pp_gallery_meta_groups` VALUES(1, \'Shop\');
					INSERT INTO `pp_gallery_meta_groups` VALUES(2, \'Basics\');
					INSERT INTO `pp_gallery_meta_groups` VALUES(3, \'Exif\');
					
					-- --------------------------------------------------------
					--
					-- Tabellenstruktur fŸr Tabelle `pp_gallery_meta_images`
					--
					
					CREATE TABLE `'.$GLOBALS['db']['db_prefix'].'gallery_meta_images` (
					  `i_id` int(11) NOT NULL,
					  `m_id` int(11) NOT NULL,
					  `value` varchar(100) NOT NULL,
					  PRIMARY KEY (`i_id`,`m_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
					
					-- --------------------------------------------------------
					--
					-- Insert User data for root user
					--
					
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'gallery_album` VALUES(1,\'u_1\', \'\', \'\', \'\', 1, \'2012-02-19 22:42:12\', 2, 0);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'gallery_folder` VALUES(1, 1, \'Userimage\', \'2012-02-21 23:50:48\', 1, 0, \'\', 0, 0, 0);
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'gallery_images` VALUES(1, \'no_image.gif\', \'_uploads/no_user_image.gif\', \'120eb50138d5e4ddec9e3d84afbf6e36\', 0, 1, \'2011-05-24 23:58:11\', \'2010-07-20 11:44:49\');
					INSERT INTO `'.$GLOBALS['db']['db_prefix'].'gallery_folder_images` VALUES(1, 1);
					
					';
    		
    		$er = $this->mysqlMultipleSetup($sql);
    		
        	// === Rights
        	// administer album
        	$this->sp->ref('Rights')->addRight('Gallery', 'administer_album');
        	$this->sp->ref('Rights')->authorizeGroup('Gallery', 'administer_album', User::getUserGroup('root'));
        	$this->sp->ref('Rights')->authorizeGroup('Gallery', 'administer_album', User::getUserGroup('admin'));

        	$this->sp->ref('Rights')->addRight('Gallery', 'create_album');
        	$this->sp->ref('Rights')->authorizeGroup('Gallery', 'create_album', User::getUserGroup('root'));
        	$this->sp->ref('Rights')->authorizeGroup('Gallery', 'create_album', User::getUserGroup('admin'));
        	
        	$this->sp->ref('Rights')->addRight('Gallery', 'create_folder');
        	$this->sp->ref('Rights')->authorizeGroup('Gallery', 'create_folder', User::getUserGroup('root'));
        	$this->sp->ref('Rights')->authorizeGroup('Gallery', 'create_folder', User::getUserGroup('admin'));
        	
        	$this->sp->ref('Rights')->addRight('Gallery', 'delete_album');
        	$this->sp->ref('Rights')->authorizeGroup('Gallery', 'delete_album', User::getUserGroup('root'));
        	$this->sp->ref('Rights')->authorizeGroup('Gallery', 'delete_album', User::getUserGroup('admin'));
        	
        	
        	$this->sp->ref('Rights')->addRight('Gallery', 'edit_album');
        	$this->sp->ref('Rights')->authorizeGroup('Gallery', 'delete_album', User::getUserGroup('root'));
        	$this->sp->ref('Rights')->authorizeGroup('Gallery', 'delete_album', User::getUserGroup('admin'));
        	
        	return $er;
        	
    	}
    	
    	/**
         * handles Post Variables in Admincenter
         */
        public function handleAdminPost() {
        	/* handle _POST */
		    if(isset($_POST['action'])){
		    	switch($_POST['action']){
		    		case 'upload':
		    			$this->executeUploads();
		    			break;
		    		case 'new_album':
		    			$this->executeNewAlbum();
		    			break;
		    		case 'new_folder':
		    			$this->executeNewFolder();
		    			break;
		    		case 'edit_album':
		    			$this->executeEditAlbum();
		    			break;
		    		case 'edit_folder':
		    			$this->executeEditFolder();
		    			break;
		    		case 'edit_image':
		    			$this->executeEditImage();
		    			break;
		    	}
		    }
        }
        
        /* --------   Add Data Functions ------ */
        /**
         * 
         * Adds Image Uploads to Database and File system
         * uses the _POST parameter of the Gallery upload Form (@see tplUploadForm)
         */
        public function executeUploads($supressMessages=false) {
        	//if($this->config['admin']['upload_just_for_admin'] && isset($_SESSION['User']) && ($_SESSION['User']['group'] == 'admin' || $_SESSION['User']['group'] == 'root')) {
        	//if(isset($_SESSION['User']) && isset($_SESSION['User']['id']) && $this->sp->ref('Rights')->c($_SESSION['User']['id'], 'Gallery', 'administer_album', $_POST['album'])) {
        	if($this->checkRight('administer_album', $_POST['album'])){
	        	if(isset($_POST['action']) && $_POST['action'] == 'upload'){
	        		// just ok if album exists or new_album name is sent
	        		if(isset($_POST['album']) && ($_POST['album'] > -1 || (isset($_POST['new_album']) && $_POST['new_album'] != ''))){
	        			$error = ($_POST['album'] == -1 && $_POST['new_album'] != '') ? !$this->newAlbum($_POST['new_album']) : false;
	        			if(!$error){
	        				$images = $this->sp->ref('UIWidgets')->getUploads(); // .------ get Uploadfiles from UIWidget
	        				//print_r($images);
	        				if($images != array()){
		        				$album_id = ($_POST['album'] == -1 && $_POST['new_album'] != '') ? $this->getAlbumId($_POST['new_album']) : $_POST['album'];
		        				if($album_id != null){
		        					//print_r($images);
		        					$return = array();
		        					foreach($images as $img){
			        					/* ----- */
			        					if($img['size'] <= $this->config['max_file_size']){ 										// check size again
			        						//if(is_uploaded_file($img['tmp_name'])){ 												// check if is uploaded - no need anymore since ftp and flash upload
			        							//$this->debugVar($img);
			        							if(preg_match("/\." . $this->config['valid_file_types'] . "$/i", $img['name'])){	// check types
			        								if(is_dir($GLOBALS['to_root'].$this->config['upload_dir'])){									// check if upload dir exists
			        									
			        									$exts = split("[/\\.]", strtolower($img['name']));
			        									
			        									$newfilepath = $this->config['upload_dir'].'/'.$this->config['upload_prefix'].str_replace(array('.', ' '), array('', ''), microtime()).'.'.$exts[count($exts)-1];
			        									
			        									if(copy($img['tmp_name'], $GLOBALS['to_root'].$newfilepath)){ // moving to final destinition (copy instead of move_uploaded_file)
			        										unlink($img['tmp_name']);
			        										//exifdata
			        										$exif = new phpExifReader($GLOBALS['to_root'].$newfilepath);
			        										//print_r($exif->getImageInfo());
			        										$exif = $exif->getImageInfo();
			        										
			        										$shot_date = (isset($exif['dateTimeDigitized'])) ?  $exif['dateTimeDigitized'] :'';
			        										
			        										if($this->newImage($img['name'], $newfilepath, $shot_date)) {//save to database	
			        											$newid = $this->getImageIdByPath($newfilepath);
			        											$return[] = $newid;
			        											
			        											if((isset($_POST['folder']) && $_POST['folder'] == -1) || !isset($_POST['folder'])){ 		// upload to album
				        											$add = ($this->addImageToAlbum($newid, $album_id) !== false);
				        											//$this->debugVar($add.'-1');
			        											} else {												 	// upload to folder
			        												// create new folder if needed
			        												if(isset($_POST['folder']) && $_POST['folder'] == -2 && isset($_POST['new_folder']) && $_POST['new_folder'] != '') {
			        													$this->newFolder($_POST['new_folder'], $album_id, '', self::STATUS_ONLINE);
			        													$_POST['folder'] = $this->getFolderByNameAndAlbum($this->sp->ref('TextFunctions')->renderUmlaute($_POST['new_folder']),  $album_id)->getId();
			        												} else if(isset($_POST['folder']) && $_POST['folder'] == -2 && $_POST['new_folder'] == '') {
			        													$error = true;
			        													$this->_msg($this->_('No folder name'));
			        												} 
			        												if(!$error) $add = $this->addImageToFolder($newid, $_POST['folder']);
			        												else $add = false;
			        											}
			        											
			        											if($add){ 	// update image meta	
			        												//add to shop
			        												if($this->config['shop']['enable_shop']){
			        													if($this->config['shop']['add_uploads_to_shop']) $this->updateMetaDataForImage($newid, $this->config['shop']['meta_visible_id'], 1);
			        												}
			        												
			        												if(isset($exif['FlashUsed'])) $this->updateMetaDataForImage($newid, $this->config['exif']['flash_id'], $exif['FlashUsed']);
			        												if(isset($exif['model'])) $this->updateMetaDataForImage($newid, $this->config['exif']['model'], $exif['model']);
			        												
			        												if(!$supressMessages) $this->_msg(str_replace('{@pp:file}', $img['name'], $this->_('UPLOAD_SUCCESS')), Messages::INFO);
			        											} else {
			        												$this->deleteImageByPath($newfilepath);
			        												$this->_msg($this->_('DATABASE_ERROR', 'database'));
			        											}
			        										} else {
			        											unlink($GLOBALS['to_root'].$newfilepath);
			        											$this->_msg($this->_('DATABASE_ERROR', 'database'));
			        											array_pop($return);
			        										}
			        									}
			        								} else $this->__($this->_('ERROR_UPLOAD_DIR_NOT_EXISTS'), Messages::RUNTIME_ERROR);
			        							} else $this->_msg(str_replace('{@pp:file}', $img['name'], $this->_('ERROR_WRONG_FORMAT')));
			        						//}
			        					} else $this->_msg(str_replace('{@pp:file}', $img['name'], $this->_('ERROR_MAX_FILE_SIZE')));
			        					/*-----*/
			        				} 
			        				//return
			        				return $return;
		        				} else $this->_msg($this->_('DATABASE_ERROR', 'database')); // no id got from name
	        				} //else $this->__($this->_('NO_UPLOADS'));
	        			} else $this->_msg($this->_('DATABASE_ERROR', 'database')); // could not create album
	        		} else $this->_msg($this->_('NO_ALBUM_NAME'));
	        	}  
        	} 
        }
        
        /**
         * Creates a new album at the database
         * uses the form returned by tplNewAlbum (@see tmpNewAlbum)
         */
        public function executeNewAlbum() {
        	if($this->checkRight('create_album')){
	        	if(isset($_POST['name']) && isset($_POST['desc']) && isset($_POST['status'])){
	        		if($this->newAlbum($_POST['name'], $_POST['desc'], $_POST['status'])){
	        			$this->_msg($this->_('NEW_ALBUM_SUCCESS'), Messages::INFO);
	        		} else $this->_msg($this->_('DATABASE_ERROR', 'database'));
	        	}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        	}
        }
        
     	/**
         * Creates a new folder at the database
         * uses the form returned by tplNewFolder (@see tmpNewFolder)
         */
        public function executeNewFolder() {
        	if(isset($_POST['f_name']) && isset($_POST['f_desc']) && isset($_POST['status']) && isset($_POST['album'])){
        		if($this->checkRight('create_folder', $_POST['album'])){
        			$error = ($_POST['album'] == -1 && $_POST['new_album'] != '') ? !$this->newAlbum($_POST['new_album']) : false;
	        		if(!$error && $this->newFolder($_POST['f_name'],  $_POST['album'], $_POST['f_desc'], $_POST['status'])){
	        			$this->_msg($this->_('NEW_ALBUM_SUCCESS'), Messages::INFO);
	        		} else $this->_msg($this->_('DATABASE_ERROR', 'database'));
        		}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        	}
        }
        
        /**
         * Updates an album 
         * uses the form returned by tplEditAlbum
         */
        public function executeEditAlbum() {
        	if(isset($_POST['id']) && isset($_POST['name']) && isset($_POST['status'])) {
        		if($this->checkRight('administer_album', $_POST['id'])){
	        		if($this->editAlbum($_POST['id'], $_POST['name'], $_POST['desc'], $_POST['status'])){
	        			$this->_msg($this->_('EDIT_ALBUM_SUCCESS'), Messages::INFO);
	        		} else {
	        			$this->_msg($this->_('DATABASE_ERROR', 'database'));
	        		}
        		} 
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        	}
        }
        
    	/**
         * Updates an folder 
         * uses the form returned by tplEditFolder
         */
        public function executeEditFolder() {
	     	if(isset($_POST['f_id']) && isset($_POST['f_name']) && isset($_POST['status']) && isset($_POST['desc'])) {
	     		
	     		$folder = $this->getFolder($_POST['f_id']);
	     		if($this->checkRight('administer_album', $folder->getAlbum)){
	     		
	        		$a_id = isset($_POST['a_id']) ? $_POST['a_id'] : -1;
	        		$sort = isset($_POST['sort']) ? $_POST['sort'] : 0;
	        		$sortDA = isset($_POST['sortDA']) ? $_POST['sortDA'] : 0;
	        		if($this->editFolder($_POST['f_id'], $_POST['f_name'], $_POST['desc'], $_POST['status'], $a_id, $sort, $sortDA)){
	        			$this->_msg($this->_('sucessfully edited folder'), Messages::INFO);
	        		} else $this->_msg($this->_('DATABASE_ERROR', 'database'));
	     		} else {
	        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
	        	}
        	}
        }
        
    	/**
         * Updates an image 
         * uses the form returned by tplEditImage
         */
        public function executeEditImage() {
        	if(isset($_POST['name']) && isset($_POST['status']) && isset($_POST['id'])) {
        		$album = $this->getAlbumForImage($_POST['id']);
        		
        		if($this->checkRight('administer_album', $album)){ // check if album can be edited
        			
	        		if($this->editImage($_POST['id'], $_POST['name'], $_POST['status'])){
	        			/* --- update meta information ---- */
	        			if(isset($_POST['meta'])){
	        				//print_r($_POST);
	        				foreach($_POST['meta'] as $name=>$m){
	        					foreach($m as $k=>$v){
	        						$meta = $this->getMetaData($k);
	        						if($meta != null){
	        							//print_r($v.'-');
	   		     						switch($meta->getType()){
	   		     							case self::META_TYPE_BOOLEAN:
	   		     								$this->updateMetaDataForImage($_POST['id'], $k, ($v=='off') ? '0' : '1');
	   		     								break;
	   		     							case self::META_TYPE_INTEGER:
	   		     								$this->updateMetaDataForImage($_POST['id'], $k, $v);
	   		     								break;
	   		     							case self::META_TYPE_STRING:
	   		     								$this->updateMetaDataForImage($_POST['id'], $k, $v);
	   		     								break;
	   		     							case self::META_TYPE_TEXT:
	   		     								$this->updateMetaDataForImage($_POST['id'], $k, $v);
	   		     								break;
	   		     						}
	           						} 
	        					}
	        				}
	        			}
	        			$this->_msg($this->_('EDIT_IMAGE_SUCCESS'), Messages::INFO);
	        		} else $this->_msg($this->_('DATABASE_ERROR', 'database'));
        		} else {
	        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
	        	}
        	}
        }
        
        /* ---------  write Data to Database ------- */
        
        /**
         * Adds a new album to the database
         * @param string $name
         * @param string $desc | (default '')
         */
        public function newAlbum($name, $desc='', $status=self::STATUS_ONLINE) {
        	if($this->checkRight('create_album') || $this->isSetup()){
        		
        		$id = $this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'gallery_album 
        								(name_'.$GLOBALS['Localization']['language'].', desc_'.$GLOBALS['Localization']['language'].', u_id, c_date, status)
        								VALUES ("'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($name)).'", 
        								"'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($desc)).'", 
        								"'.$_SESSION['User']['id'].'", NOW(), 
        								"'.$status.'")');
        		
        		if($id !== false) {
        			$error = !($this->sp->ref('Rights')->authorizeUser('Gallery', 'administer_album', $_SESSION['User']['id'], $id) && 
        						$this->sp->ref('Rights')->authorizeUser('Gallery', 'create_folder', $_SESSION['User']['id'], $id));
        						
        			if($error) {
        				$this->deleteAlbum($id);
        				return false;
        			} else return true;
        		} else return false;
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
    	/**
         * Adds a new folder to the database
         * @param string $name
         * @param string $desc | (default '')
         */
        public function newFolder($name, $album_id, $desc, $status=self::STATUS_ONLINE) {
        	$album_id = ($album_id == -1 && $_POST['new_album'] != '') ? $this->getAlbumId($_POST['new_album']) : $album_id;
        	if($this->checkRight('create_folder', $album_id) || $this->isSetup()){
        		return ($this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'gallery_folder 
        								(`name`,`u_id`, `datum`, `status`, `a_id`, `desc`)
        								VALUES ("'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($name)).'", 
        								"'.$_SESSION['User']['id'].'", NOW(), 
        								"'.$status.'", 
        								"'.mysql_real_escape_string($album_id).'", 
        								"'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($desc)).'")') !== false);
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
        /**
         * 
         * Updates an album in the database
         * @param int $id | id of album
         * @param string $name | new name
         * @param string $desc | new desc
         * @param int $status |@see const
         */
        public function editAlbum($id, $name, $desc, $status){
        	if($this->checkRight('administer_album', $id)){
        		
        		return ($this->mysqlUpdate('UPDATE '.$GLOBALS['db']['db_prefix'].'gallery_album 
        								SET `name_'.$GLOBALS['Localization']['language'].'`="'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($name)).'", 
        									`desc_'.$GLOBALS['Localization']['language'].'`="'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($desc)).'",
        									`status`="'.mysql_real_escape_string($status).'" WHERE a_id="'.mysql_real_escape_string($id).'";') !== false);
	        } else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        		
        }
        
        /**
         * 
         * Sets Thumbnail for Album $id
         * @param $id
         * @param $thumb_id
         */
        public function setAlbumThumb($id, $thumb_id){
        	if($this->checkRight('administer_album', $id)){
        		if($thumb_id == -1) return false;
        		return ($this->mysqlUpdate('UPDATE '.$GLOBALS['db']['db_prefix'].'gallery_album 
        								SET `thumb`="'.mysql_real_escape_string($thumb_id).'"
        								WHERE a_id="'.mysql_real_escape_string($id).'"') !== false);
	        } else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        		
        }
        
     	/**
         * 
         * Updates an folder in the database
         * @param int $id | id of folder
         * @param string $name | new name
         * @param string $desc | new desc
         * @param int $status |@see const
         */
        public function editFolder($id, $name, $desc, $status, $a_id=-1, $sort=0, $sortDA=0){
        	$folder = $this->getFolder($id);
        	if($folder != null && $this->checkRight('administer_album', $folder->getAlbum())) {
	        	$e = true;
	        	if($a_id != -1) {
	        		$e = $this->mysqlUpdate('UPDATE '.$GLOBALS['db']['db_prefix'].'gallery_folder
	        								SET a_id="'.mysql_real_escape_string($a_id).'" WHERE f_id="'.mysql_real_escape_string($id).'";');
	        	}
	        	return $e && ($this->mysqlUpdate('UPDATE '.$GLOBALS['db']['db_prefix'].'gallery_folder
        								SET `name`="'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($name)).'", 
        									`desc`="'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($desc)).'", 
        									`status`="'.mysql_real_escape_string($status).'",
        									`sort`="'.mysql_real_escape_string($sort).'",
        									`sortDA`="'.mysql_real_escape_string($sortDA).'"
        									WHERE `f_id`="'.mysql_real_escape_string($id).'";') !== false);
	        } else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
	        	
        }
        
     	/**
         * 
         * Sets Thumbnail for Folder $id
         * @param $id
         * @param $thumb_id
         */
        public function setFolderThumb($id, $thumb_id){
        	$folder = $this->getFolder($id);
        	if($folder != null && $this->checkRight('administer_album', $folder->getAlbum())) {
        		if($thumb_id == -1) return false;
        		return ($this->mysqlUpdate('UPDATE '.$GLOBALS['db']['db_prefix'].'gallery_folder 
        								SET `thumb`="'.mysql_real_escape_string($thumb_id).'"
        								WHERE f_id="'.mysql_real_escape_string($id).'"') !== false);
	        } else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        			
        }
        
        /**
         * 
         * Writes data of the new image (has to be already at final position) to the Database
         * @param string $name
         * @param string $path
         */
        private function newImage($name, $path, $shot_date='NULL'){
        	if(is_file($GLOBALS['config']['root'].$path)){
        		return ($this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'gallery_images
        									(name, path, hash, status, u_id, u_date, shot_date) VALUES
        									("'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($name)).'", 
        									 "'.mysql_real_escape_string($path).'", 
        									 "'.md5_file($GLOBALS['config']['root'].$path).'", 
        									 "'.self::STATUS_ONLINE.'", 
        									 "'.$_SESSION['User']['id'].'", 
        									 NOW(), 
        									 "'.mysql_real_escape_string($shot_date).'")') !== false);
        	}
        }
        
        private function setImageStatus($id, $status){
       	 	if($this->checkRight('administer_album', $this->getAlbumForImage($id))){
        		return ($this->mysqlUpdate('UPDATE '.$GLOBALS['db']['db_prefix'].'gallery_images
        								SET status="'.mysql_real_escape_string($status).'" WHERE i_id="'.mysql_real_escape_string($id).'";') !== false);
	        } else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
     	/**
         * 
         * Updates an image in the database
         * @param int $id | id of album
         * @param string $title | new title
         * @param int $status |@see const
         */
        public function editImage($id, $title, $status){
        	if($this->checkRight('administer_album', $this->getAlbumForImage($id))){
        		return ($this->mysqlUpdate('UPDATE '.$GLOBALS['db']['db_prefix'].'gallery_images
        								SET name="'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($title)).'", 
        									status="'.mysql_real_escape_string($status).'" WHERE i_id="'.mysql_real_escape_string($id).'";') !== false);
	        } else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        			
        }
        
        public function addImageToAlbum($i_id, $a_id){
        	if($this->checkRight('administer_album', $a_id)){
        		if($i_id > 0 && $a_id > 0){
        			return ($this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'gallery_album_images (i_id, a_id) VALUES ("'.mysql_real_escape_string($i_id).'", "'.mysql_real_escape_string($a_id).'")') !== false);
        		} else return false;
	        } else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        			
        }
        
    	public function addImageToFolder($i_id, $f_id){
    		$folder = $this->getFolder($f_id);
    		if($folder != null && $this->checkRight('administer_album', $folder->getAlbum())){
	        	if($i_id > 0 && $f_id > 0){
	        		return ($this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'gallery_folder_images (i_id, f_id) VALUES ("'.mysql_real_escape_string($i_id).'", "'.mysql_real_escape_string($f_id).'")') !== false);
	        	} else return false;
	        } else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
    	/**
         * 
         * Deletes Image by given Path
         * @param $path
         */
        public function deleteImageByPath($path){
        	// right check in deleteImageById()
        	if(is_file($GLOBALS['config']['root'].$path)){
        		$this->deleteImageById($this->getImageIdByPath($path));
        	}
        }
        
        /**
         * 
         *  deletes images + meta and image links from database 
         *  unlinks images from filesystem and deletes image cache
         * @param int $id
         */
        public function deleteImageById($id){
        	if($this->checkRight('administer_album', $this->getAlbumForImage($id))){
	        	if($id > -1 && $id != ''){
	        		if($this->mysqlDelete('DELETE FROM '.$GLOBALS['db']['db_prefix'].'gallery_album_images WHERE i_id="'.mysql_real_escape_string($id).'"') &&
	        			$this->mysqlDelete('DELETE FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder_images WHERE i_id="'.mysql_real_escape_string($id).'"')) {
	        				
	        			$a = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_images WHERE i_id = "'.mysql_real_escape_string($id).'"');
	        			if(is_array($a)) {
	        				$path = $a['path']; 
	        				if($this->mysqlDelete('DELETE FROM '.$GLOBALS['db']['db_prefix'].'gallery_images WHERE i_id="'.mysql_real_escape_string($id).'"')){
	        					// delete file
	        					if(is_file($GLOBALS['config']['root'].$path)) unlink($GLOBALS['config']['root'].$path);
			        			// delete cache
			        			$this->sp->ref('Image')->clearCache($path);
			        			// delete meta
			        			return $this->deleteMetaDataByImage($id);
	        				} else return false;
	        			} else return false;
	        		} else return false;
	        	} else return false;
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
        /**
         * 
         * Deletes all Meta Data to Image Id
         * @param $iId
         */
        private function deleteMetaDataByImage($iId){
        	if($this->checkRight('administer_album', $this->getAlbumForImage($id))){
        		return $this->mysqlDelete('DELETE FROM '.$GLOBALS['db']['db_prefix'].'gallery_meta_images WHERE i_id="'.mysql_real_escape_string($iId).'"');
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
        /**
         * 
         * Deletes Album, all containing Images and all Image links from database
         * @param $id
         */
        public function deleteAlbum($id){
        	if($this->checkRight('administer_album', $id) && $this->checkRight('delete_album', $id)){
              	$error = false;
	        	if($id != '' && $id != -1){
		        	$f = $this->getFolderByAlbum($id);
		        	if($f != array()){
		        		foreach($f as $f1){
		        			$tmp = !$this->deleteFolder($f1->getId());
		        			$error = $error && $tmp;
		        		}
		        	}
	        		
	        		$i = $this->getImagesByAlbum($id, -1);
		        	if(isset($i)){
		        		foreach($i as $img) {
		        			$tmp = !$this->deleteImageById($img->getId());
		        			$error = $error && $tmp;
		        		}
		        	}
		        	if($error) return false;
		        	else return $this->mysqlDelete('DELETE FROM '.$GLOBALS['db']['db_prefix'].'gallery_album WHERE a_id="'.mysql_real_escape_string($id).'"');
	        	}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
    	/**
         * 
         * Deletes Folder, all containing Images and all Image links from database
         * @param $id
         */
        public function deleteFolder($id){
        	$folder = $this->getFolder($id);

        	if($folder != null && $this->checkRight('administer_album', $folder->getAlbum())){
	        	$error = false;
	        	if($id != '' && $id != -1){
		        	$i = $this->getImagesByFolder($id, -1, -1, -1);
		        	if(isset($i)){
		        		foreach($i as $img) {
		        			$tmp = !$this->deleteImageById($img->getId());
		        			$error = $error && $tmp;
		        		}
		        	}
		        	if($error) return false;
		        	else return $this->mysqlDelete('DELETE FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder WHERE f_id="'.mysql_real_escape_string($id).'"');
	        	}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
        /**
         * Updates or adds Meta Data of given Image
         * @param $image_id
         * @param $meta_id
         * @param $value
         */
        public function updateMetaDataForImage($iId, $mId, $value){
        	if($this->checkRight('administer_album', $this->getAlbumForImage($iId))){
	        	$i = $this->getMetaDataForImage($iId, $mId);
	        	if(isset($i)){
	        		//update
	        		return ($this->mysqlUpdate('UPDATE '.$GLOBALS['db']['db_prefix'].'gallery_meta_images 
	        											SET value="'.mysql_real_escape_string($value).'" 
	        											WHERE i_id="'.mysql_real_escape_string($iId).'" 
	        												AND m_id="'.mysql_real_escape_string($mId).'"') !== false);
	        	} else {
	        		//insert
	        		return ($this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'gallery_meta_images 
	        											(i_id, m_id, value) VALUES ("'.mysql_real_escape_string($iId).'",
	        																		"'.mysql_real_escape_string($mId).'",
	        																		"'.mysql_real_escape_string($value).'");') !== false);
	        	}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
        
        /* --------   Data Functions ------ */
        /**
         * returns Meta Object Data from Database
         * @param int $iId
         * @param int $mId
         */
        private function getMetaDataForImage($iId, $mId){
        	$m = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_meta_images mi 
        									LEFT JOIN '.$GLOBALS['db']['db_prefix'].'gallery_meta m ON mi.m_id = m.m_id
        								 WHERE mi.i_id="'.mysql_real_escape_string($iId).'" AND mi.m_id="'.mysql_real_escape_string($mId).'"');
        	if(is_array($m)){
        		return new GalleryMeta($m['m_id'], $m['name'], $m['type'], $m['group'], $m['desc'], $m['i_id'], $m['value']);
        	}
        	return null;
        }
        
        /**
         * 
         * returnes Meta Data Object
         * @param $mId
         */
        private function getMetaData($mId){
        	$m = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_meta m
        								 WHERE m.m_id="'.mysql_real_escape_string($mId).'"');
        	if(is_array($m)){
        		return new GalleryMeta($m['m_id'], $m['name'], $m['type'], $m['group'], $m['desc']);
        	}
        	return null;
        }
        
        /**
         * returnes array of meta Objects by ImageId
         * ordered by Group_id, 
         * @param $iId
         * @param $withGroupNames | if true group names will be added
         */
        private function getMetaDataByImage($iId){
        	$metas = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_meta_images mi
        										LEFT JOIN '.$GLOBALS['db']['db_prefix'].'gallery_meta m ON mi.m_id = m.m_id 
        										WHERE mi.i_id="'.mysql_real_escape_string($iId).'"');
        	if(is_array($metas)){
        		$r = array();
        		foreach($metas as $m){
        			$r[] = new GalleryMeta($m['m_id'], $m['name'], $m['type'], $m['group'], $m['desc'], $m['i_id'], $m['value']);
        		}
        		return $r;
        	} else return array();
        }
        
        /**
         * 
         * Gets all meta data and the values of the specified image
         * @param $id | 
         */
        private function getAllMetaData($id) {
        	$metas = $this->mysqlArray('SELECT *, mg.name as groupName, m.name as metaName, m.m_id as metaId
        										FROM '.$GLOBALS['db']['db_prefix'].'gallery_meta m 
        										LEFT JOIN '.$GLOBALS['db']['db_prefix'].'gallery_meta_groups mg ON m.group = mg.mg_id 
        										LEFT JOIN (SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_meta_images WHERE i_id="'.mysql_real_escape_string($id).'") mi on m.m_id = mi.m_id
        										ORDER BY m.group ASC, m.order ASC');
        	if(is_array($metas)){
        		$r = array();
        		foreach($metas as $m){
        			$r[] = new GalleryMeta($m['metaId'], $m['metaName'], $m['type'], $m['group'], $m['desc'], $m['i_id'], $m['value'], $m['readonly']);
        			$r[count($r)-1]->setGroupName($m['groupName']);
        			$r[count($r)-1]->setLabel($m['desc']);
        		}
        		return $r;
        	} else return array();
        }
        
        /**
         * returnes id of image by given path
         * @param unknown_type $path
         */
        private function getImageIdByPath($path){
        	$a = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_images WHERE path = "'.mysql_real_escape_string($path).'"');
        	if(is_array($a) && isset($a['i_id'])) return $a['i_id'];
        	else return -1;
        }
        
        /**
         * returnes Image Path By Image Id
         * @param $id
         */
        public function getImagePathById($id){
        	$i =  $this->getImage($id);
        	return ($i == null) ? null : $i->getPath();
        }
                
        /**
         * returnes Id of album with given name
         * @param string $name | name of the album
         * @param string $lang | (default: '') if '' it will use the active language
         */
        public function getAlbumId($name, $lang=''){
        	if($lang=='') $lang = $GLOBALS['Localization']['language'];
        	$a = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_album WHERE name_'.mysql_real_escape_string($lang).' = "'.mysql_real_escape_string($name).'"');
        	if(is_array($a) && isset($a['a_id'])){
        		return $a['a_id'];
        	} else return null;
        }
        
        /**
         * gets All Album from Database and returnes the data in an array
         * @param boolean $display_all_languages | if true all languages will be returned - otherwise just the active language (default: false)
         * @param boolean $just_online | if false all Albums will be returned 
         * @param string $order | orders output after this string 
         */
        private function getAlbumNames($display_all_languages=false, $just_online=true, $order='a_id') {
        	$r = array();
        	$online = ($just_online) ? 'WHERE status ="'.self::STATUS_ONLINE.'"' : '';
        	$album = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_album '.$online.' ORDER BY '.$order);
        	if(is_array($album)){
	        	foreach($album as $a){
	        		$add = array('id'=>$a['a_id'], 'name'=>$a['name_'.$GLOBALS['Localization']['language']], 'desc'=>$a['desc_'.$GLOBALS['Localization']['language']]);
	        		if(!$just_online) $add['status'] = $a['status'];
	        		if($display_all_languages){
	        			//TODO: add all Languages
	        		}
	        		$r[] = $add;
	        		unset($add);
	        	}
        	}
        	return $r;
        }
        
        /**
         * returnes just all albums you can access
         */
        private function getMyAlbumNames($id=-1, $order='a_id'){
        	$r = array();
        	$id = ($id==-1) ? $_SESSION['User']['id'] : $id;
        	$album = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_album ORDER BY '.$order);
        	if(is_array($album)){
	        	foreach($album as $a){
	        		if($this->checkRight('administer_album', $a['a_id'], $id) && ($a['status'] != self::STATUS_SERVICE_ALBUM)){//|| in_array($_SESSION['User']['group'], array('root', 'admin')))){
		        		$add = array('id'=>$a['a_id'], 'name'=>$a['name_'.$GLOBALS['Localization']['language']], 'desc'=>$a['desc_'.$GLOBALS['Localization']['language']]);
		        		if(!$just_online) $add['status'] = $a['status'];
		        		if($display_all_languages){
		        			//TODO: add all Languages
		        		}
		        		$r[] = $add;
		        		unset($add);
	        		}
	        	}
        	}
        	return $r;
        }
        
        /**
         * returnes album id for give Image
         * @param $id
         */
        private function getAlbumForImage($id){
        	$q = $this->mysqlRow('SELECT f.a_id folder_album, ai.a_id image_album FROM `'.$GLOBALS['db']['db_prefix'].'gallery_images` gi
        									LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'gallery_folder_images` fi ON fi.i_id = gi.i_id
        									LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'gallery_album_images` ai ON ai.i_id = gi.i_id
        									LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'gallery_folder` f ON f.f_id = fi.f_id
        						WHERE gi.i_id="'.mysql_real_escape_string($id).'"');
        	if($q != array()){
        		return ($q['image_album'] == null) ? $q['folder_album'] : $q['image_album'];
        	} else return -1;
        }
        
        /**
         * 
         * returnes an array of GalleryImage objects from the chosen $album
         * @param $a_id | album id
         * @param $page
         * @param $per_page
         */
        private function getImagesByAlbum($a_id, $page, $per_page=-1, $sort=-1, $sortDA=-1, $status=-1){
        	$status = ($status == -1) ? '' : ' AND status="'.mysql_real_escape_string($status).'" ';
        	$limit = ($per_page == -1) ? '' : 'LIMIT '.(mysql_real_escape_string($page-1)*mysql_real_escape_string($per_page)).', '.mysql_real_escape_string($per_page).';';
        	$return = array();
        	
        	$order = '';
        	if($sort == 'name'){
        		$asc = ($sortDA == 'desc') ? 'DESC' : 'ASC';
        		$order = 'name '.$asc.'';
        	} else {
        		$asc = ($sortDA == 'desc') ? 'DESC' : 'ASC';
        		$order = 'i.shot_date '.$asc.', i.i_id DESC';
        	}
        	
        	$a = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_album_images ai LEFT JOIN 
        									'.$GLOBALS['db']['db_prefix'].'gallery_images i ON ai.i_id = i.i_id WHERE ai.a_id = "'.mysql_real_escape_string($a_id).'" '.$status.'
        								ORDER BY '.$order.' '.$limit);
        	if(is_array($a)){
	        	foreach($a as $image){
	        		$return[] = new GalleryImage($image['i_id'], $image['name'], $image['path'], $image['hash'], $image['status'], $image['u_date'], $image['u_id'], $image['shot_date']);
	        	}
        	}
        	return $return;
        }
        
        /**
         * returnes images and folder of specified album with one query
         * @param $a_id
         * @param $page
         * @param $per_page
         * @param $status
         */
        private function getContentOfAlbum($a_id, $page, $per_page=-1, $sort=-1, $sortDA=-1, $status=-1) {
        	$status = ($status == -1) ? '' : ' AND status="'.mysql_real_escape_string($status).'" ';
        	$limit = ($per_page == -1) ? '' : 'LIMIT '.(mysql_real_escape_string($page-1)*mysql_real_escape_string($per_page)).', '.mysql_real_escape_string($per_page).';';
        	$order = '';
        	
        	if($sort == 'name' || $sort == self::SORT_NAME){
        		$asc = ($sortDA == 'desc' || $sortDA == self::SORT_DESC) ? 'DESC' : 'ASC';
        		$order = 'name '.$asc.'';
        	} else {
        		$asc = ($sortDA == 'desc' || $sortDA == self::SORT_DESC) ? 'DESC' : 'ASC';
        		$order = 'order_date '.$asc.', i_i_id DESC';
        	}

        	$return = array();

        	// colums names are hardcoded in this query - change if neccesary
        	$a = $this->mysqlArray('(SELECT ai.a_id i_a_id, i.i_id i_i_id, i.path i_path, i.hash i_hash, i.status i_status, i.u_date i_u_date, i.u_id i_u_id, i.name name, i.shot_date order_date, \'\' f_f_id, \'\' f_a_id, \'\' f_datum, \'\' f_u_id, \'\' f_status, \'\' f_desc, \'\' f_thumb, \'\' f_sort, \'\' f_sortDA  FROM '.$GLOBALS['db']['db_prefix'].'gallery_album_images ai 
        												LEFT JOIN '.$GLOBALS['db']['db_prefix'].'gallery_images i ON ai.i_id = i.i_id 
        												WHERE ai.a_id = "'.mysql_real_escape_string($a_id).'" '.$status.'
        										) UNION (
        										SELECT \'\', \'\', \'\', \'\', \'\', \'\', \'\', f.name name, f.datum order_date, f.f_id f_f_id, f.a_id f_a_id,  f.datum f_datum, f.u_id f_u_id, f.status f_status, f.desc f_desc, f.thumb f_thumb, f.sort, f.sortDA FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder f 
        												WHERE f.a_id = "'.mysql_real_escape_string($a_id).'" '.$status.'
        									)
        								ORDER BY '.$order.' '.$limit);

        	if(is_array($a)){
        		foreach($a as $content){
        			if($content['i_i_id'] != '') { //image
        				$return[] = new GalleryImage($content['i_i_id'], $content['name'], $content['i_path'], $content['i_hash'], $content['i_status'], $content['i_u_date'], $content['i_u_id'], $content['i_order_date']);
        			} else if($content['f_f_id'] != '') { // folder
        				$count = $this->mysqlArray('SELECT COUNT(*) i_count FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder_images WHERE f_id="'.$content['f_f_id'].'"');
        				$c = (is_array($count)) ? $count[0]['i_count'] : 0; 
        				$return[] = new GalleryFolder($content['f_f_id'], $content['f_a_id'], $content['name'], $content['f_desc'], $content['f_status'], $content['f_thumb'], $content['f_u_id'], $c, $content['f_sort'], $content['f_sortDA']);
        			}
        		}
        	}
        	return $return;
        
        }
        
        /**
         * 
         * returnes an array of GalleryImage objects from the chosen $album
         * @param $a_id | album id
         * @param $page
         * @param $per_page
         */
        private function getImagesByFolder($f_id, $page, $per_page=-1, $sort=-1, $sortDA=-1, $status=-1){
        	$status = ($status == -1) ? '' : ' AND status="'.mysql_real_escape_string($status).'" ';
        	$limit = ($per_page == -1) ? '' : 'LIMIT '.(mysql_real_escape_string($page-1)*mysql_real_escape_string($per_page)).', '.mysql_real_escape_string($per_page).';';
        	
        	$order = '';
        	if($sort == 'name' || $sort == self::SORT_NAME){
        		$asc = ($sortDA == 'desc' || $sortDA == self::SORT_DESC) ? 'DESC' : 'ASC';
        		$order = 'name '.$asc.'';
        	} else {
        		$asc = ($sortDA == 'desc' || $sortDA == self::SORT_DESC) ? 'DESC' : 'ASC';
        		$order = 'i.shot_date '.$asc.', i.i_id DESC';
        	}

        	$a = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder_images fi LEFT JOIN 
        									'.$GLOBALS['db']['db_prefix'].'gallery_images i ON fi.i_id = i.i_id WHERE fi.f_id = "'.mysql_real_escape_string($f_id).'" '.$status.'
        								ORDER BY '.$order.' '.$limit);
        	if(is_array($a)){
	        	foreach($a as $image){
	        		$return[] = new GalleryImage($image['i_id'], $image['name'], $image['path'], $image['hash'], $image['status'], $image['u_date'], $image['u_id'], $image['shot_date']);
	        	}
        	}
        	return $return;
        }
        
        /**
         * 
         * returnes an array of Folders from the chosen $album
         * format [['id'=>$id, 'name'=>$name 'datum'=>$datum, 'creator'=>$creator], [...]]
         * @param $a_id | album id
         */
        private function getFolderByAlbum($a_id, $with_date=false){
        	$return = array();
        	$a = $this->mysqlArray('SELECT *  FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder
        									WHERE a_id = "'.mysql_real_escape_string($a_id).'"');
        	if(is_array($a) && $a != array()){
        		
        		foreach($a as $folder){
        			$f = new GalleryFolder($folder['f_id'], $folder['a_id'], $folder['name'], $folder['desc'], $folder['status'], $this->getImage($folder['thumb']), $folder['u_id'], -1, $folder['sort'], $folder['sortDA']);
        			if($with_date){
        				$x = $this->mysqlArray('SELECT *
        												FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder_images gfi
        												LEFT JOIN '.$GLOBALS['db']['db_prefix'].'gallery_images gi ON gfi.i_id = gi.i_id 
        												WHERE gfi.f_id = "'.$folder['f_id'].'" LIMIT 0, 1');
        				
        				$c = $this->mysqlArray('SELECT COUNT(*) counti FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder_images WHERE f_id="'.$folder['f_id'].'"');
        				if($c != array() && isset($c[0]['counti'])) $f->setImageCount($c[0]['counti']);
        				
        				if($x != array() && isset($x[0]['u_date']) && isset($x[0]['shot_date'])){
        					$date = ($x[0]['shot_date'] == '0000-00-00 00:00:00') ? $x[0]['u_date'] : $x[0]['shot_date'];
        					$f->setDate($date);
        				}
        			}
        			
               		$return[] = $f;
               		unset($f);
        		}
        		
        		unset($tmp);
        	}
        	
        	/* bubblesort */
        	for ( $i = 0; $i < count($return); $i++ )  {  
			   for ($j = 0; $j < count($return); $j++ )  
			   {  
			      if ($return[$i]->getDate() < $return[$j]->getDate())  
			      {  
			         $temp = $return[$i];  
			         $return[$i] = $return[$j];  
			         $return[$j] = $temp;  
			      }  
			   }  
			}  
        	return $return;
        }
        
        /**
         * @see GalleryDataHandler->getFolderByAlbumAndName();
         */
        public function getFolderByNameAndAlbum($name, $a_id){
        	return $this->dataHandler->getFolderByAlbumAndName($a_id, $name);
        }
        
        /**
         * 
         * returnes select field for folders of selected album
         * @param $a_id
         */
        public function getFolderSelectByAlbum($a_id){
        	if($this->checkRight('administer_album', $a_id)){
        		$folders = $this->getFolderByAlbum($a_id);
        	
	        	$t = $this->sp->ref('UIWidgets')->getWidget('Select');
	        	if($this->checkRight('create_folder', $a_id)) $t->addOption($this->_('New Folder'), -2);	
	        	$t->addOption($this->_('No Folder'), -1);	
	        	if($folders != array()){
		        	foreach($folders as $folder){
		        		$t->addOption($folder->getName(), $folder->getId());		
		        	}
	        	}
	        	
	        	$t->setName('folder');
	        	$t->setLabel($this->_('FOLDER'));
	        	$t->setId('folders');
	        	
	        	return $t->render();
        	} else {
        		return '';
        	}
        }
        
        /**
         * @see GalleryDataHandler->getImageCountForALbum
         */
        private function getImageCountForAlbum($a_id, $status= -1){
        	return $this->dataHandler->getImageCountForAlbum($a_id, $status);
        }
    	
    	/**
         * returnes total count of images in folders and count of folders in specified album
         * @param $a_id | album id
         */
        private function getFolderCountForAlbum($a_id, $status= -1){
        	return array('image_count'=>$this->dataHandler->getImageCountForAlbum($a_id, $status),
        				'folder_count'=>$this->dataHandler->getFolderCountForAlbum($a_id, $status));
        }
        
    	/**
         * @see GalleryDataHandler->getImageCountForFolder
         */
        private function getImageCountForFolder($f_id, $status= -1){
        	return $this->dataHandler->getImageCountForFolder($f_id, $status);
        }
        
        /** 
         * @see GalleryDataHandler->getImageById() 
         */
        public function getImage($id){
        	return $this->dataHandler->getImageById($id);
        }
    
        /**
         * 
         * returnes First, Previous, next and last Image from Image iId
         * @param $iId
         * @param $aId
         * @param $status
         */
        private function getSpecialImageIds($iId, $aId, $fId=-1, $sort=-1, $sortDA=-1, $status=self::STATUS_ONLINE){
        	if($fId == -1) $imgs = $this->getImagesByAlbum($aId, 0, -1, $sort, $sortDA, $status);
        	else $imgs = $this->getImagesByFolder($fId, 0, -1, $sort, $sortDA, $status);
        	
        	$lastimg = null;
        	$prev = null;
        	$next = null;
        	$first = null;
        	if($imgs != array()){
	        	foreach($imgs as $img){
	        		if($first == null) $first = $img;
	        		if($img->getId() == $iId) $prev = $lastimg;
	        		if(isset($lastimg) && $lastimg->getId() == $iId) $next = $img;
	        		$lastimg = $img;
	        		if($next != null && $prev != null) break;
	        	}
        	}
        	return array('first'=>$first, 'prev'=>$prev, 'next'=>$next, 'last'=>$lastimg);
        }
        
        /**
         * 
         * returnes an album in an array
         * @param $id
         */
        private function getAlbum($id){
        	if($this->checkRight('administer_album', $id)){
	        	$a = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_album WHERE a_id="'.mysql_real_escape_string($id).'"');
	        	if(is_array($a)){
	        		
	       			return new GalleryAlbum($a['a_id'], $a['name_'.$GLOBALS['Localization']['language']], $a['desc_'.$GLOBALS['Localization']['language']], $a['status'], $this->getImage($a['thumb'])); 

	        	} else return null;
        	} else return null;
        }
        
    	/**
         * @see GalleryDataHandler->getFolderById();
         */
        private function getFolder($id){
        	return $this->dataHandler->getFolderById($id);
        	/*$a = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder WHERE f_id="'.mysql_real_escape_string($id).'"');
        	if(is_array($a)){
        		if($this->checkRight('administer_album', $a['a_id'])){
       				return new GalleryFolder($a['f_id'], $a['a_id'], $a['name'], $a['desc'], $a['status'], $this->getImage($a['thumb']), $a['u_id'], -1, $a['sort'], $a['sortDA']);
        		} else return null; 
        	} else return null;*/
        }
        
     	/**
         * 
         * returnes all album with the given state - default ONLINE
         * @param $status
         * @param checkRight |Êif false rightCheck will be disabled and a list of all Albums will be returned
         */
        private function getAlbums($status=self::STATUS_ONLINE, $checkRight=true){
        	$al = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_album WHERE status="'.mysql_real_escape_string($status).'"');
        	if(is_array($al)){
        		$r = array();
        		foreach($al as $a){
        			if(!$checkRight || $this->checkRight('administer_album', $a['a_id'])){
        				$r[] = new GalleryAlbum($a['a_id'], $a['name_'.$GLOBALS['Localization']['language']], $a['desc_'.$GLOBALS['Localization']['language']], $a['status'], $this->getImage($a['thumb']));
        			}
        				 
        		}
       			return  $r;
        	} else return null;
        }
        
        /**
         * 
         * returnes image count for specified album
         * @param $id
         */
        private function getImageCountByAlbum($id){
        	$count = $this->mysqlRow('SELECT COUNT(*) as count FROM '.$GLOBALS['db']['db_prefix'].'gallery_album_images WHERE a_id="'.mysql_real_escape_string($id).'"');
        	if(is_array($count)) return $count['count'];
        	else return -1;
        }
        
        /**
         * returnes string to status
         * @param int $status
         */
        private function statusToString($status){
        	if($status==self::STATUS_ONLINE) return 'online';
        	else return 'offline';
        }
        
        private function getMetaType($id){
        	switch($id){
        		case self::META_TYPE_BOOLEAN:
        			return 'checkbox';
        			break;
        		case self::META_TYPE_INTEGER:
        			return 'inputField';
        			break;
        		case self::META_TYPE_STRING:
        			return 'inputField';
        			break;
        		case self::META_TYPE_TEXT:
        			return 'textArea';
        			break;
        		case self::META_TYPE_READONLY:
        			return 'text';
        			break;
        		default:
        			return 'inputField';
        			break;
        	}
        }

        /* --------   Admin Template Functions ------ */
        
        public function tplEditAlbum($id, $link='', $ajax=false){
        	if($this->checkRight('administer_album', $id)){
	        	if($id != -1) {
	        		$alb = $this->getAlbum($id);
	        		if(isset($alb)) {
	        			$tpl = new ViewDescriptor($this->config['tpl']['admin/edit_album']);
	        			
	        			if($ajax) $tpl->showSubView('ajax_button');
	        			
	        			$img = $alb->getThumb();
	        			if($img != null) {
	        				$s = new SubViewDescriptor('edit_album_thumb');
	        				$s->addValue('img_id', $img->getId());
	        				$s->addValue('img_path', $img->getPath());
	        				$tpl->addSubView($s);
	        				unset($s);
	        			} else $tpl->showSubView('edit_album_no_thumb');
	        			
	        			$tpl->addValue('id', $alb->getId());
	        			$tpl->addValue('name', $alb->getName());
	        			$tpl->addValue('desc', $alb->getDescription());
	        			$tpl->addValue('link', $link);
	        			$tpl->addValue('status', $this->tplGetStatusSelect($alb->getStatus()));
	        			
	        			return $tpl->render();
	        		} else return '';
	        	} else {
	        		if($ajax) return str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core'));
	        		else {
	        			$this->__(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
	        			return '';
	        		}
	        	}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return $this->_('You are not authorized', 'rights');
        	}
        }
        
    	public function tplEditFolder($id, $link='', $ajax=false){
        	if($id != -1) {
        		$fol = $this->getFolder($id);
        		if($fol != null && $this->checkRight('administer_album', $fol->getAlbum())){
	        		if(isset($fol)) {
	        			$tpl = new ViewDescriptor($this->config['tpl']['admin/edit_folder']);
	        			
	        			if($ajax) $tpl->showSubView('ajax_button');
	        			
	        			$img = $fol->getThumb();
	        			if($img != null) {
	        				$s = new SubViewDescriptor('edit_folder_thumb');
	        				$s->addValue('img_id', $img->getId());
	        				$s->addValue('img_path', $img->getPath());
	        				$tpl->addSubView($s);
	        				unset($s);
	        			} else $tpl->showSubView('edit_folder_no_thumb');
	        			
	        			$tpl->addValue('id', $fol->getId());
	        			$tpl->addValue('sort', $fol->getSort());
	        			$tpl->addValue('sortDA', $fol->getSortDA());
	        			$tpl->addValue('name', $fol->getName());
	        			$tpl->addValue('link', $link);
	        			$tpl->addValue('desc', $fol->getDesc());
	        			$tpl->addValue('status', $this->tplGetStatusSelect($fol->getStatus()));
	        			
	        			return $tpl->render();
	        		} else return '';
        		} else {
        			$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        			return $this->_('You are not authorized', 'rights');
        		}
        	} else {
        		if($ajax) return str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core'));
        		else {
        			$this->__(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
        			return '';
        		}
        	}
        }
        
        private function checkMetaVisibility($group) {
        	return ($group != $this->config['shop']['meta_group'] || $this->config['shop']['enable_shop']);
        }
        
    	public function tplEditImage($id, $link='', $ajax=false){
    		if($this->checkRight('administer_album', $this->getAlbumForImage($id))){
	        	if($id != -1) {
	        		$alb = $this->getImage($id);
	        		if(isset($alb)) {
	        			$tpl = new ViewDescriptor($this->config['tpl']['admin/edit_image']);
	        			
	        			if($ajax) $tpl->showSubView('ajax_button');
	        			//if($this->config['shop']['enable_shop']) $tpl->showSubView('shop');
	        			
	        			$tpl->addValue('id', $alb->getId());
	        			$tpl->addValue('name', $alb->getName());
	        			$tpl->addValue('shot_date', str_replace(':', '.', $alb->getShotDate()));
	        			$tpl->addValue('link', $link);
	        			$tpl->addValue('status', $this->tplGetStatusSelect($alb->getStatus()));
	        			
	        			/* --- get Meta Data --- */
	        			$meta = $this->getAllMetaData($id); // get Meta with ImageValues
	        			if($meta != array()){
	        				$lastGroup = '';
	        				$start = true;
	        				$groupSV = new SubViewDescriptor('meta_groups');
	        				foreach($meta as $m){
	        					if($m->getGroup() != $lastGroup) {
	        						if(!$start) {
	        							if($this->checkMetaVisibility($lastGroup)) $tpl->addSubView($groupSV);
	        							unset($groupSV);
	        							$groupSV = new SubViewDescriptor('meta_groups');
	        							$groupSV->addValue('name', $m->getGroupName());
	        						}
									$lastGroup = $m->getGroup();
	        					} 
	        					if($start) {
	        						$groupSV->addValue('name', $m->getGroupName());
	        						$start = false;
	        					}
	        					
	        					if($m->isReadOnly()) $m->setType(self::META_TYPE_READONLY);
	        					$metaSV = new SubViewDescriptor('meta_item');
	        					$metaSV->addValue('id', $m->getId());
	        					$metaSV->addValue('name', $m->getName());
	        					$metaSV->addValue('label', $m->getLabel());
	        					$metaSV->addValue('group', $m->getGroup());
	        					$metaSV->addValue('group_name', $m->getGroupName());
	        					$metaSV->addValue('type', $this->getMetaType($m->getType()));
	        					/* --- parse Value --- */
	        					if(($m->getType() == self::META_TYPE_BOOLEAN)){
	        						$metaSV->addValue('checked', ($m->getValue() == '1') ? 'true' : 'false');
	        						/* --- checkbox fix (checkboxes return 'off') */
	        						$cb_fix = new SubViewDescriptor('checkbox_fix');
	        						$cb_fix->addValue('group', $m->getGroup());
	        						$cb_fix->addValue('id', $m->getId());
	        						$metaSV->addSubView($cb_fix);
	        						unset($cb_fix);
	        					}  else {
	        						$metaSV->addValue('value', $m->getValue());
	        					}
	        					
	        					$groupSV->addSubView($metaSV);
	        					unset($metaSV);
	        				}
	        				if($this->checkMetaVisibility($lastGroup))$tpl->addSubView($groupSV);
	        				unset($groupSV);
	        			}
	        			
	        			return $tpl->render();
	        		} else return '';
	        	} else {
	        		if($ajax) return str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core'));
	        		else {
	        			$this->__(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
	        			return '';
	        		}
	        	}
    		} else {
    			$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return $this->_('You are not authorized', 'rights');
    		}
        }
        
        /**
         * 
         * returnes a renderes Album from the database
         * @param int $id | id of the album
         * @param int $page | page number
         * @param int $rows | images per page
         * @param boolean $ajax | if true the ajax dynamic will be activated
         * @param string $link | link to gallery page
         */
        public function adminGetAlbum($id, $page=-1, $rows=-1, $sort=-1, $sortDA=-1, $ajax=false, $link=''){
        	if($this->checkRight('administer_album', $id)){
	        	$GLOBALS['extra_js'][] = 'foundation.js'; 
	        	$GLOBALS['extra_js'][] = 'jquery.contextMenu.js'; 
	        	$GLOBALS['extra_js'][] = 'jquery.address-1.4.min.js'; 
	        	if($id != -1){
	        		$all_images = $this->getImageCountForAlbum($id);
	        		$folder_count = $this->getFolderCountForAlbum($id);
	        		
	        		$f_count = ($folder_count != array()) ? $folder_count['folder_count'] : 0;
	        		
		        	$per_page = ($rows == -1) ? $this->config['per_page']['admin'] : $rows;
		        	$page = ($page==-1 || $page > ceil(($all_images+$f_count)/$per_page)) ? 1: $page;
		        	
		        	$tpl = new ViewDescriptor($this->config['tpl']['admin/view_album']);
		        	$tpl->addValue('link', $link);
		        	
		        	$all = $this->getContentOfAlbum($id, $page, $per_page, $sort, $sortDA);
		        	
		        	$pagina_link = (strpos($link, '?') > 0) ? $link.'&page={page}' : $link.'?page={page}'; 
		        	$tpl->addValue('pagina_link', $pagina_link);
		        	$tpl->addValue('pagina_active', $page);
		        	$tpl->addValue('pagina_count', ceil(($all_images+$f_count)/$per_page));
		        	$tpl->addValue('active_album', $id);
		        	$tpl->addValue('folder_count', $f_count);
		        	$tpl->addValue('image_count', ($folder_count != array()) ? $all_images+$folder_count['image_count'] : '0');	        	
		        	
		        	
		        	if($all != array()){
		        		foreach($all as $content){
		        			$t = new SubViewDescriptor('images');
	
		        			if(get_class($content) == 'GalleryImage'){ // image
	
		        				$t1 = new SubViewDescriptor('images_image');
				        		if($content->getStatus() == self::STATUS_OFFLINE) $t1->showSubView('offline');
				        		$t1->addValue('id', $content->getId());
				        		$t1->addValue('path', $content->getPath());
				        		$t1->addValue('name', $content->getName());
				        		$t1->addValue('short_name', $this->sp->ref('TextFunctions')->cropText($content->getName(), 22));
				        		$t1->addValue('status', $content->getStatus());
				        		$t1->addValue('u_date', $content->getUploadDate());
				        		/* --- meta data --- */
				        		$meta = $this->getMetaDataByImage($content->getId());
				        		if($meta != null){
				        			foreach($meta as $m) {
				        				if($this->config['shop']['enable_shop'] && $m->getId() == $this->config['shop']['meta_visible_id'] && $m->getValue() == '1') $t1->showSubView('shop');
				        			}
				        		}
				        		$t->addSubView($t1);
				        		unset($t1);
		        			} else if(get_class($content) == 'GalleryFolder'){ // folder
	
		        				$t2 = new SubViewDescriptor('images_folder');
			        			$t2->addValue('id', $content->getId());
			        			$t2->addValue('name',  $content->getName());
			        			$t2->addValue('datum',  $content->getDate());
			        			$t2->addValue('thumb',  ($content->getThumb() != 0) ? $this->getImage($content->getThumb())->getPath() : '_uploads/'.$this->config['admin']['folder_no_image_path']);
			        			$t2->addValue('count',  $content->getImageCount());
	
			        			if($content->getStatus() == self::STATUS_OFFLINE) $t2->showSubView('offline');
			        			
			        			$t->addSubView($t2);
			        			unset($t2);
		        			}
				        	$tpl->addSubView($t);
		        		}
		        	}
		        	
		        	if(count($all) == 0) $tpl->showSubView('no_images');
		        	
		        	return $tpl->render();
	        	} else {
	        		if($ajax) return str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core'));
	        		else {
	        			$this->__(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
	        			return '';
	        		}
	        	}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return $this->_('You are not authorized', 'rights');
        	}
        }
        
         /**
         * 
         * returnes a renderes Folder from the database
         * @param int $id | id of the folder
         * @param int $page | page number
         * @param int $rows | images per page
         * @param boolean $ajax | if true the ajax dynamic will be activated
         * @param string $link | link to gallery page
         */
    	public function adminGetFolder($id, $page=-1, $rows=-1, $sort=-1, $sortDA=-1, $ajax=false, $link=''){
    		$folder = $this->getFolder($id);
    		if($folder != null && $this->checkRight('administer_album', $folder->getAlbum())){
	        	$GLOBALS['extra_js'][] = 'foundation.js'; 
	        	$GLOBALS['extra_js'][] = 'jquery.contextMenu.js'; 
	        	$GLOBALS['extra_js'][] = 'jquery.address-1.4.min.js'; 
	        	if($id != -1){
	        		$all_images = $this->getImageCountForFolder($id);
		        	$per_page = ($rows == -1) ? $this->config['per_page']['admin'] : $rows;
		        	$page = ($page==-1 || $page > ceil($all_images/$per_page)) ? 1: $page;
		        	
		        	$tpl = new ViewDescriptor($this->config['tpl']['admin/view_folder']);
		        	$tpl->addValue('link', $link);
		        	
		        	$pagina_link = (strpos($link, '?') > 0) ? $link.'&page={page}' : $link.'?page={page}'; 
		        	$tpl->addValue('pagina_link', $pagina_link);
		        	$tpl->addValue('pagina_active', $page);
		        	$tpl->addValue('pagina_count', ceil($all_images/$per_page));
		        	$tpl->addValue('active_album', $id);
		        	$tpl->addValue('image_count', $all_images);
		        	
		        	$images = $this->getImagesByFolder($id, $page, $per_page, $sort, $sortDA);
		        	if($images != array()){
			        	foreach($images as $img){
			        		$t = new SubViewDescriptor('images');
			        		if($img->getStatus() == self::STATUS_OFFLINE) $t->showSubView('offline');
			        		$t->addValue('id', $img->getId());
			        		$t->addValue('path', $img->getPath());
			        		$t->addValue('name', $img->getName());
			        		$t->addValue('short_name', $this->sp->ref('TextFunctions')->cropText($img->getName(), 22));
			        		$t->addValue('status', $img->getStatus());
			        		$t->addValue('u_date', $img->getUploadDate());
			        		/* --- meta data --- */
			        		$meta = $this->getMetaDataByImage($img->getId());
			        		if($meta != null){
			        			foreach($meta as $m) {
			        				if($this->config['shop']['enable_shop'] && $m->getId() == $this->config['shop']['meta_visible_id'] && $m->getValue() == '1') $t->showSubView('shop');
			        			}
			        		}
			        		$tpl->addSubView($t);
			        		unset($t);
			        	}
		        	}
		        	if(count($images) == 0) $tpl->showSubView('no_images');
		        	
		        	return $tpl->render();
	        	} else {
	        		if($ajax) return str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core'));
	        		else {
	        			$this->__(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
	        			return '';
	        		}
	        	}
    		} else {
    			$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return $this->_('You are not authorized', 'rights');
    		}
        }
        
        /**
         * 
         * returnes a rendered Image 
         * @param int $id |id of the image
         * @param boolean $ajax | if true the ajax dynamic will be activated
         * @param string $link | link to gallery page
         */
        public function adminGetImage($id, $aId, $fId=-1, $sort=-1, $sortDA=-1, $ajax=false, $link=''){
        	if($this->checkRight('administer_album', $aId)){
	        	$GLOBALS['extra_js'][] = 'foundation.js'; 
	        	$GLOBALS['extra_js'][] = 'jquery.contextMenu.js'; 
	        	$GLOBALS['extra_js'][] = 'jquery.address-1.4.min.js'; 
	        	if($id != -1) {
	        		$img = $this->getImage($id);
	        		
	        		if(isset($img)) {
	        			
	        			$info = getimagesize($GLOBALS['config']['root'].$img->getPath());
	        			$specialImages = $this->getSpecialImageIds($id, $aId, $fId, $sort, $sortDA, -1);
	
						$tpl = new ViewDescriptor($this->config['tpl']['admin/view_image']);
	        			$tpl->addValue('id', $img->getId());
		        		$tpl->addValue('path', $img->getPath());
		        		$tpl->addValue('backlink', $_SESSION['history']['prev_page']);
		        		$tpl->addValue('user', $img->getUserId());
		        		$tpl->addValue('name', $img->getName());
		        		$tpl->addValue('width', $info[0]);
		        		$tpl->addValue('height', $info[1]);
		        		$tpl->addValue('mime', $info['mime']);
		        		$tpl->addValue('channels', $info['channels']);
		        		$tpl->addValue('bits', $info['bits']);
		        		if(isset($specialImages['next'])) {
		        			$sv = new SubViewDescriptor('next_exists');
		        			$sv->addValue('nextImageId',  $specialImages['next']->getId());
		        			$tpl->addSubView($sv);
		        			unset($sv);
		        		} else $tpl->showSubView('next_nExists');
		        		if(isset($specialImages['prev'])) {
		        			$sv = new SubViewDescriptor('prev_exists');
		        			$sv->addValue('prevImageId', $specialImages['prev']->getId());
		        			$tpl->addSubView($sv);
		        			unset($sv);
		        		} else $tpl->showSubView('prev_nExists');
		        		
		        		$tpl->addValue('short_name', $this->sp->ref('TextFunctions')->cropText($img->getName(), 22));
		        		$tpl->addValue('status', $this->statusToString($img->getStatus()));
		        		$tpl->addValue('date', $img->getUploadDate());
		        		
	        			return $tpl->render();
	        		} else return '';
	        	} else {
	        		if($ajax) return str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core'));
	        		else {
	        			$this->__(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
	        			return '';
	        		}
	        	}
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return $this->_('You are not authorized', 'rights');
        	}
        }
        
		/**
		 * Returnes whole admin center
		 * @param string $link
		 * @param int $album
		 */
        public function tplAdmin($link='', $album=-1){
        	if(isset($_SESSION['User'])){
	        	//$GLOBALS['extra_css'][] = $this->config['css_file_admin'];
	        	$GLOBALS['extra_js'][] = $this->config['js_file_admin'];
	        	$GLOBALS['extra_js'][] = 'foundation.js'; 
	        	$GLOBALS['extra_js'][] = 'jquery.contextMenu.js'; 
	        	$GLOBALS['extra_js'][] = 'jquery.address-1.4.min.js'; 
	        	
	        	$t = new ViewDescriptor($this->config['tpl']['admin/main']);
	        	
	        	$t->addValue('link', $link);
	        	// ---- messages
	        	$t->addValue('delete_success_message', $this->_('DELETE_IMAGE_SUCCESS'));
	        	$t->addValue('delete_error_message',  $this->_('DELETE_IMAGE_ERROR'));
	        	$t->addValue('delete_success_message_album', $this->_('DELETE_ALBUM_SUCCESS'));
	        	$t->addValue('delete_error_message_album',  $this->_('DELETE_ALBUM_ERROR'));
	        	$t->addValue('delete_success_message_folder', $this->_('Folder deleted successfully'));
	        	$t->addValue('delete_error_message_folder',  $this->_('Folder could not be deleted'));
	        	$t->addValue('set_thumb_folder_success',  $this->_('Thumb for folder set successfully'));
	        	$t->addValue('set_thumb_folder_error',  $this->_('Thumb for folder could not be set'));
	        	$t->addValue('set_thumb_album_success',  $this->_('Thumb for album set successfully'));
	        	$t->addValue('set_thumb_album_error',  $this->_('Thumb for album could not be set'));
	        	
	        	$t->addValue('sort',  $this->config['sort']['admin']);
	        	$t->addValue('sortDA',  $this->config['sortDA']['admin']);
	        	
	        	$t->addValue('active_album', $album);
	        	$t->addValue('active_page', (isset($_GET['page'])) ? $_GET['page'] : 1);
	        	$t->addValue('active_image', (isset($_GET['image'])) ? $_GET['image'] : -1);
	        	//print_r( $album);
	        	if($this->checkRight('create_album')) $t->showSubView('createAlbum');
	        	
	        	return $t->render();
        	} else return '';
        }
        
        /**
         * returnes rendered albums for sidebar at gallery admincenter
         * @param $page
         * @param $special
         * @param $id
         */
     	public function tplSidebarAlbums($page, $special='', $id=''){
        	if(isset($_SESSION['User'])){
	     		$tpl = new ViewDescriptor($this->config['tpl']['admin/sidebar_albums'] );
	        	
	        	$gr = (isset($_SESSION['User']['group'])) ? $_SESSION['User']['group'] : -1;
	
	        	if(in_array(strtolower($gr), $this->config['admin']['can_see_extra_albums'])) {
	        		$a = new SubViewDescriptor('root');
	        		
	        		$id = ($special=='user' && $id != '') ? $id : -1;
	        		
	        		$a->addValue('user', $this->sp->ref('User')->tplGetUserDropdown($id, true));
	        		
	        		$tpl->addSubView($a);
	        		unset($a);
	        	}
	        	if($special == 'serviceAlben'){
	        		
	        	} else {
		        	if($special=='user' && $id != ''){
		        		if(in_array(strtolower($gr), $this->config['admin']['can_see_extra_albums'])) {
		        			$alb = $this->getMyAlbumNames($id);
		        		} else {
		        			$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
		        			$alb = $this->getMyAlbumNames();
		        		}
		        	} else {
		        		$alb = $this->getMyAlbumNames();
		        	}
		        	
	        	
		        	$count = count($alb);
	
	        		$per_page = $this->config['per_page']['sidebar'];
		        	$page = ($page==-1 || $page > ceil(($count)/$per_page)) ? 1: $page;
	        		if(ceil($count/$per_page) > 1){
	        			$p = new SubViewDescriptor('pagina');
	        			$p->addValue('pagina_active', $page);
		        		$p->addValue('pagina_count', ceil($count/$per_page));
	        			$tpl->addSubView($p);
	        			unset($p);
	        		}
	        		
	        		$alb = array_slice($alb, ($page-1)*$per_page, $per_page);
		        	
		        	foreach($alb as $a){
		        		if($this->checkRight('administer_album', $a['id']) && ($a['status'] != self::STATUS_SERVICE_ALBUM  || $_SESSION['User']['group'] == 'root')){//|| in_array($_SESSION['User']['group'], array('root', 'admin')))){
		        		
			        		$ad = new SubViewDescriptor('albums');
			        		$ad->addValue('name', $a['name']);
			        		$ad->addValue('desc', $a['desc']);
			        		$ad->addValue('id', $a['id']);
			        		
			        		if($a['status'] == self::STATUS_OFFLINE) $ad->showSubView('hidden');
			        		
			        		$ad->addValue('selected', ($a['id']==$album) ? 'class="selected"' : '');
			        		$tpl->addSubView($ad);
			        		
			        		//folders
			        		$folders = $this->getFolderByAlbum($a['id'], true);
			        		if($folders != array()){
			        			$folder_sv = new SubViewDescriptor('folder_exists');
			        			foreach($folders as $folder){
			        				$f = new SubViewDescriptor('folders');
			        				$f->addValue('name', $folder->getName());
			        				$f->addValue('id', $folder->getId());
			        				$f->addValue('a_id', $a['id']);
			        				
			        				$folder_sv->addSubView($f);
			        				unset($f);
			        			}
			        			$ad->addSubView($folder_sv);
			        			unset($folder);
			        		}
			        		unset($ad);
		        		}
		        	}
	        	}
	        	return $tpl->render();
        	} else return '';
        }
        
     	/**
         * Returns Upload Form
         */
        public function tplUploadForm($link='', $album=-1, $ajax=false){
        	if($this->checkRight('administer_album', $album)){
	        	
	        	$GLOBALS['extra_css'][] = $this->config['css_file_admin'];
	        	$GLOBALS['extra_js'][] = $this->config['js_file_admin'];
	        	$GLOBALS['extra_js'][] = 'foundation.js'; 
	        	
	        	$t = new ViewDescriptor($this->config['tpl']['admin/upload']);
	        	$t->addValue('link', $link);
	        	$t->addValue('types', $this->config['valid_file_types']);
	        	
	        	$sel = $this->sp->ref('UIWidgets')->getWidget('Select');
	        	$sel->setLabel($this->_('ALBUM'));
	        	$sel->setName('album');
	        	$sel->setId('albums');
	        	
	        	$cats = $this->getAlbumNames(false, false, 'name_'.$GLOBALS['Localization']['language']);
	        	if($this->checkRight('create_album')) $cats1 = array(array('caption'=>$this->_('NEW_ALBUM'), 'value'=>-1));
	        	foreach($cats as $cat){
	        		if($this->checkRight('administer_album', $cat['id'])){
	        			if($cat['id'] == $album) $cats1[] = array('caption'=>$cat['name'], 'value'=>$cat['id'], 'selected'=>'true');
	        			else $cats1[] = array('caption'=>$cat['name'], 'value'=>$cat['id']);
	        		}
	        	}
	        	$sel->addOptions($cats1);
	        	
	        	if($ajax) $t->showSubView('ajax_button');
	        	        	
	        	$t->addValue('albums_select', $sel->render());
	        	$t->addValue('max_file_size', $this->config['max_file_size']);
	        	$t->addValue('max_uploads', $this->config['max_uploads']);
	        	return $t->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return $this->_('You are not authorized', 'rights');
        	}
        }
        
        /**
         * Returns form for new Album
         */
        public function tplNewAlbum($link='', $ajax){
        	if($this->checkRight('create_album')){
	        	$GLOBALS['extra_css'][] = $this->config['css_file_admin'];
	        	$GLOBALS['extra_js'][] = $this->config['js_file_admin'];
	        	$GLOBALS['extra_js'][] = 'foundation.js'; 
	        	
	        	$t = new ViewDescriptor($this->config['tpl']['admin/new_album']);
	        	$t->addValue('status', $this->tplGetStatusSelect());
	        	$t->addValue('link', $link);
	        	
	        	if($ajax) $t->showSubView('ajax_button');
	        	
	        	return $t->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return $this->_('You are not authorized', 'rights');
        	}
	    }
    	
    	/**
         * Returns form for new folder
         */
        public function tplNewFolder($link='', $album=-1, $ajax){
        	if($this->checkRight('administer_album', $album) || $this->checkRight('create_folder', $album)){
        		$GLOBALS['extra_css'][] = $this->config['css_file_admin'];
	        	$GLOBALS['extra_js'][] = $this->config['js_file_admin'];
	        	$GLOBALS['extra_js'][] = 'foundation.js'; 
	        	
	        	$t = new ViewDescriptor($this->config['tpl']['admin/new_folder']);
	        	
	        	$sel = $this->sp->ref('UIWidgets')->getWidget('Select');
	        	$sel->setLabel($this->_('ALBUM'));
	        	$sel->setName('album');
	        	$sel->setId('albums');
	        	
	        	$cats = $this->getAlbumNames(false, false, 'name_'.$GLOBALS['Localization']['language']);
	        	
	        	if($this->checkRight('create_album')) $cats1 = array(array('caption'=>$this->_('NEW_ALBUM'), 'value'=>-1));
	        	
	        	foreach($cats as $cat){
	        		if($this->checkRight('administer_album', $cat['id'])){
	        			if($cat['id'] == $album) $cats1[] = array('caption'=>$cat['name'], 'value'=>$cat['id'], 'selected'=>'true');
	        			else $cats1[] = array('caption'=>$cat['name'], 'value'=>$cat['id']);
	        		}
	        	}
	        	$sel->addOptions($cats1);
	        	
	        	if($ajax) $t->showSubView('ajax_button');
	        	        	
	        	$t->addValue('albums_select', $sel->render());
	        	$t->addValue('status', $this->tplGetStatusSelect());
	        	$t->addValue('link', $link);
	        	
	        	return $t->render();
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return $this->_('You are not authorized', 'rights');
        	}
        }
        
        /**
         * returnes a dropdown of all states defined in self
         */
        public function tplGetStatusSelect($status=-1) {
        	$t = $this->sp->ref('UIWidgets')->getWidget('Select');
        	$o = array();

        	if($status == self::STATUS_ONLINE) $o[] = array('caption'=>'online', 'value'=>self::STATUS_ONLINE, 'selected'=>'');
        	else $o[] = array('caption'=>'online', 'value'=>self::STATUS_ONLINE);
        	
        	if($status == self::STATUS_OFFLINE) $o[] = array('caption'=>'offline', 'value'=>self::STATUS_OFFLINE, 'selected'=>'');
        	else $o[] = array('caption'=>'offline', 'value'=>self::STATUS_OFFLINE);
        	
        	if(isset($_SESSION['User']) && isset($_SESSION['User']['group']) && in_array($_SESSION['User']['group'], array('root', 'admin'))){
        		if($status == self::STATUS_SERVICE_ALBUM) $o[] = array('caption'=>'user_album', 'value'=>self::STATUS_SERVICE_ALBUM, 'selected'=>'');
        		else $o[] = array('caption'=>'user_album', 'value'=>self::STATUS_SERVICE_ALBUM);
        	}
        	
        	$t->addOptions($o);
        	$t->setName('status');
        	$t->setLabel($this->_('STATUS'));
        	$t->setId('status');
        	return $t->render();
        }
        
        /* -- user Template functions --- */
        /**
         * 
         * Renders Template for all Albums
         */
        public function tplGetAlbums() {
        	$GLOBALS['extra_js'][] = 'jquery.address-1.4.min.js'; 
        	$GLOBALS['extra_js'][] = 'foundation.js'; 
        	
        	$albums = $this->getAlbums();
        	$tpl = new ViewDescriptor($this->config['tpl']['view_albums']);

        	if(isset($albums)){
        		foreach($albums as $album){
        			if($this->checkRight('administer_album', $album->getId())){
		        		$aSV = new SubViewDescriptor('albums');
		        		$aSV->addValue('id', $album->getId());
		        		$aSV->addValue('name', $album->getName());
		        		$aSV->addValue('desc', $album->getDescription());
		        		$aSV->addValue('status', $album->getStatus());
		        		$aSV->addValue('imageCount', $this->getImageCountForAlbum($album->getId()));
		        		$tpl->addSubView($aSV);
		        		unset($aSV);
        			}
	        	}
        	}
        	
        	return $tpl->render();
        }
        
        /**
         * 
         * returnes rendered Template for Album
         * @param $id
         */
        public function tplGetAlbum($id, $page=1, $sort=-1, $sortDA=-1){
        	$GLOBALS['extra_css'][] = 'gallery.js'; 
        	if($id > -1) {
        		$album = $this->getAlbum($id);
        		if($album->getStatus() == self::STATUS_ONLINE){
        			/* -- pagina -- */
        			$all_images = $this->getImageCountForAlbum($id, self::STATUS_ONLINE);
	        		$folder_count = $this->getFolderCountForAlbum($id, self::STATUS_ONLINE);

	        		$f_count = ($folder_count != array()) ? $folder_count['folder_count'] : 0;

	        		$per_page = $this->config['per_page']['user'];
		        	$page = ($page==-1 || $page > ceil(($all_images+$f_count)/$per_page)) ? 1: $page;
		        	
		        	$all = $this->getContentOfAlbum($id, $page, $per_page, $sort, $sortDA);
		        	/*$pagina_link = (strpos($link, '?') > 0) ? $link.'&page={page}' : $link.'?page={page}'; 
		        	$tpl->addValue('pagina_link', $pagina_link);
		        	$tpl->addValue('pagina_active', $page);
		        	$tpl->addValue('pagina_count', ceil(($all_images+$f_count)/$per_page));
		        	$tpl->addValue('active_album', $id);
		        	$tpl->addValue('folder_count', $f_count);
		        	$tpl->addValue('image_count', ($folder_count != array()) ? $all_images+$folder_count['image_count'] : '0');	        	
		        	*/
		        	
        			/*$all_images = $this->getImageCountForAlbum($id, self::STATUS_ONLINE);
		        	$per_page = $this->config['per_page']['user'];
		        	$page = ($page==-1 || $page > ceil($all_images/$per_page)) ? 1: $page;
		        	
	        		$a = $this->getImagesByAlbum($id, $page, $this->config['per_page']['user'], self::STATUS_ONLINE);*/
	        		$tpl = new ViewDescriptor($this->config['tpl']['view_album']);
		        	
	        		if(ceil($all_images+$f_count/$per_page) > 1){
	        			$p = new SubViewDescriptor('pagina');
        				$p->addValue('pagina_active', $page);
		        		$p->addValue('pagina_count', ceil($all_images+$f_count/$per_page));
	        			$tpl->addSubView($p);
	        			unset($p);
	        		}
	        		//$tpl->addValue('link', $link);
		        	$tpl->addValue('id', $id);
	        		$tpl->addValue('name', $album->getName());
	        		$tpl->addValue('description', $album->getDescription());
        			$tpl->addValue('height', $this->config['client']['thumb_height']);
        			$tpl->addValue('width', $this->config['client']['thumb_width']);
		        	$tpl->addValue('image_count', $all_images);
		        	$tpl->addValue('folder_count', $f_count);
		        	$tpl->addValue('albumId', $id);
		        	
	        		
	        		if($all != array()){
		        		foreach($all as $content){
		        			$t = new SubViewDescriptor('images');
	
		        			if(get_class($content) == 'GalleryImage'){ // image
	
		        				$t1 = new SubViewDescriptor('images_image');
				        		$t1->addValue('id', $content->getId());
				        		$t1->addValue('path', $content->getPath());
	        					$t1->addValue('albumId', $id);
	        					$t1->addValue('height', $this->config['client']['thumb_height']);
	        					$t1->addValue('width', $this->config['client']['thumb_width']);
	        					$t1->addValue('div_height', $this->config['client']['thumb_height']+40); // +20 because of space + 20 because of text
	        					$t1->addValue('div_width', $this->config['client']['thumb_width']+20);
				        		$t1->addValue('name', $content->getName());
				        		$t1->addValue('short_name', $this->sp->ref('TextFunctions')->cropText($content->getName(),  $this->config['client']['thumb_title_length']));
				        		/* --- meta data --- */
				        		
				        		$t->addSubView($t1);
				        		unset($t1);
		        			} else if(get_class($content) == 'GalleryFolder'){ // folder

		        				$t2 = new SubViewDescriptor('images_folder');
			        			$t2->addValue('id', $content->getId());
	        					$t2->addValue('div_height', $this->config['client']['thumb_height']+20); // +20 because of space
	        					$t2->addValue('div_width', $this->config['client']['thumb_width']+20);
			        			$t2->addValue('name',  $content->getName());
			        			$t2->addValue('datum',  $content->getDate());
			        			$t2->addValue('thumb',  ($content->getThumb() != 0) ? $this->getImage($content->getThumb())->getPath() : '_uploads/'.$this->config['admin']['folder_no_image_path']);
			        			$t2->addValue('count',  $content->getImageCount());
			        			
			        			if($this->config['client']['render_lightbox']){
			        				$imgs = $this->getImagesByFolder($content->getId(), -1, -1, $content->getSort(), $content->getSortDA(), self::STATUS_ONLINE);
			        				if(is_array($imgs) && $imgs != array()){
			        					$t3 = new SubViewDescriptor('lightbox_links');
			        					foreach($imgs as $img){
			        						$t4 = new SubViewDescriptor('lightbox_link');
			        						$t4->addValue('path', $img->getPath());
			        						$t4->addValue('name', $img->getName());
			        						
			        						$t3->addSubView($t4);
			        						
			        						unset($t4);
			        					}
			        					$t2->addSubView($t3);
			        					unset($t3);
			        				}
			        				
			        			}
			        			if($this->config['client']['calc_first_folder_image']){
				        			$imgs = $this->getImagesByFolder($content->getId(), -1, -1, $content->getSort(), $content->getSortDA(), self::STATUS_ONLINE);
				        			if(is_array($imgs) && $imgs != array()){
				        				$t2->addValue('fi_id',  $imgs[0]->getId());
				        			}
			        			}
	
			        			if($content->getStatus() == self::STATUS_OFFLINE) $t2->showSubView('offline');
			        			
			        			$t->addSubView($t2);
			        			unset($t2);
		        			}
				        	$tpl->addSubView($t);
		        		}
		        	}
	        		if(count($all) == 0) $tpl->showSubView('no_images');
		        	
		        	/*if($a != array()){
	        			foreach($a as $i){
	        				$iSV = new SubViewDescriptor('image');
	        				$iSV->addValue('id', $i->getId());
	        				$iSV->addValue('path', $i->getPath());
	        				$iSV->addValue('albumId', $id);
	        				$iSV->addValue('height', $this->config['client_thumb_height']);
	        				$iSV->addValue('width', $this->config['client_thumb_width']);
	        				$iSV->addValue('div_height', $this->config['client_thumb_height']+40); // +20 because of space + 20 because of text
	        				$iSV->addValue('div_width', $this->config['client_thumb_width']+20);
	        				$iSV->addValue('name', $i->getName());
			        		$iSV->addValue('short_name', $this->sp->ref('TextFunctions')->cropText($i->getName(),  $this->config['client_thumb_title_length']));
			        		$iSV->addValue('status', $i->getStatus());
			        		$iSV->addValue('u_date', $i->getUploadDate());
	        				$tpl->addSubView($iSV);
	        				unset($iSV);
	        			}
	        		} else $tpl->showSubView('no_images');*/
	        		return $tpl->render();
        		} else return 'OFFLINE';
        	} else {
        		if($ajax) return str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core'));
        		else {
        			$this->__(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
        			return '';
        		}
        	}
        }
        
     /**
         * 
         * returnes rendered Template for Album
         * @param $id
         */
        public function tplGetFolder($id, $page=1, $sort=-1, $sortDA=-1){
        	$GLOBALS['extra_css'][] = 'gallery.js'; 
        	if($id > -1) {
        		$folder = $this->getFolder($id);
        		if($folder->getStatus() == self::STATUS_ONLINE){
        			/* -- pagina -- */
        			$all_images = $this->getImageCountForFolder($id, self::STATUS_ONLINE);

	        		$per_page = $this->config['per_page_folder']['user'];
		        	$page = ($page==-1 || $page > ceil(($all_images)/$per_page)) ? 1: $page;
		        	
		        	//$all = $this->getContentOfAlbum($id, $page, $per_page, $sort, $sortDA);
		        	$all = $this->getImagesByFolder($id, $page, $per_page, $folder->getSort(), $folder->getSortDA(), self::STATUS_ONLINE);
		        	
		        	$tpl = new ViewDescriptor($this->config['tpl']['view_folder']);

		        	if(ceil($all_images/$per_page) > 1){
	        			$p = new SubViewDescriptor('pagina');
        				$p->addValue('pagina_active', $page);
		        		$p->addValue('pagina_count', ceil($all_images/$per_page));
	        			$tpl->addSubView($p);
	        			unset($p);
	        		}

	        		$tpl->addValue('id', $id);
	        		$tpl->addValue('name', $folder->getName());
	        		$tpl->addValue('description', $folder->getDesc());
        			$tpl->addValue('height', $this->config['client']['thumb_height']);
        			$tpl->addValue('width', $this->config['client']['thumb_width']);
		        	$tpl->addValue('image_count', $all_images);
		        	$tpl->addValue('folderId', $id);

		        	if($all != array()){
		        		foreach($all as $content){
	
	        				$t1 = new SubViewDescriptor('images_image');
			        		$t1->addValue('id', $content->getId());
			        		$t1->addValue('path', $content->getPath());
        					$t1->addValue('albumId', $id);
        					$t1->addValue('height', $this->config['client']['thumb_height']);
        					$t1->addValue('width', $this->config['client']['thumb_width']);
        					$t1->addValue('div_height', $this->config['client']['thumb_height']+40); // +20 because of space + 20 because of text
        					$t1->addValue('div_width', $this->config['client']['thumb_width']+20);
			        		$t1->addValue('name', $content->getName());
			        		$t1->addValue('short_name', $this->sp->ref('TextFunctions')->cropText($content->getName(),  $this->config['client']['thumb_title_length']));
			        		
			        		$tpl->addSubView($t1);
			        		unset($t1);
		        		}
		        	}
	        		if(count($all) == 0) $tpl->showSubView('no_images');
		        	
	        		return $tpl->render();
        		} else return 'OFFLINE';
        		
        	} else {
        		if($ajax) return str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core'));
        		else {
        			$this->__(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
        			return '';
        		}
        	}
        }
        
        public function tplGetImage($id, $aId, $fId=-1, $sort=-1, $sortDA=-1){
       	 	if($id != -1) {
        		$img = $this->getImage($id);
        		
        		if(isset($img)) {
        			if($img->getStatus() == self::STATUS_ONLINE){
	        			
	        			$info = getimagesize($GLOBALS['config']['root'].$img->getPath());
						$specialImages = $this->getSpecialImageIds($id, $aId, $fId, $sort, $sortDA);
	        			
	        			$tpl = new ViewDescriptor($this->config['tpl']['view_image']);
	        			
	        			$tpl->addValue('id', $img->getId());
		        		$tpl->addValue('path', $img->getPath());
		        		$tpl->addValue('user', $img->getUserId());
		        		$tpl->addValue('name', $img->getName());
		        		$tpl->addValue('width', $info[0]);
		        		$tpl->addValue('height', $info[1]);
		        		
		        		$tpl->addValue('short_name', $this->sp->ref('TextFunctions')->cropText($img->getName(), 22));
		        		$tpl->addValue('status', $this->statusToString($img->getStatus()));
		        		$tpl->addValue('date', $img->getUploadDate());
		        		
		        		if(isset($specialImages['next'])) {
		        			$sv = new SubViewDescriptor('next_exists');
		        			$sv->addValue('nextImageId',  $specialImages['next']->getId());
		        			$tpl->addSubView($sv);
		        			unset($sv);
		        		} else $tpl->showSubView('next_nExists');
		        		if(isset($specialImages['prev'])) {
		        			$sv = new SubViewDescriptor('prev_exists');
		        			$sv->addValue('prevImageId', $specialImages['prev']->getId());
		        			$tpl->addSubView($sv);
		        			unset($sv);
		        		} else $tpl->showSubView('prev_nExists');
		        		/* meta */
		        		//TODO: Meta
		        		
	        			return $tpl->render();
	        			
        			} else return 'OFFLINE';
        		} else return '';
        	} else {
        		if($ajax) return str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core'));
        		else {
        			$this->__(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
        			return '';
        		}
        	}
        }
        
        /** ---------------  SMALL STUFF --------------*/
        /**
         * renders small Album 
         * not used any more
         * just used for User service 
         * @param $al
         * @param $page
         * @param $sort
         * @param $sortDA
         * @param $click
         */
        public function tplSmallAlbum($al, $page, $sort, $sortDA, $click) {
        	if($this->checkRight('administer_album', $album)){
        		$sort = ($sort == -1) ? 'date' : $sort;
        		$sortDA = ($sortDA == -1) ? 'desc' : $sortDA;
        		
        		$album = $this->getAlbum($al);
        		if($album == null) $album = $this->getAlbum($this->getAlbumId($al));

        		if($album != null){
        			$tpl = new ViewDescriptor($this->config['tpl']['small/album']);
        			$id = $album->getId();
        			
        			$all_images = $this->getImageCountForAlbum($id);
	        		$folder_count = $this->getFolderCountForAlbum($id);
	        		
	        		$f_count = ($folder_count != array()) ? $folder_count['folder_count'] : 0;
	        		
		        	$per_page = $this->config['per_page']['small'];
		        	$page = ($page==-1 || $page > ceil(($all_images+$f_count)/$per_page)) ? 1: $page;
		        			        	
		        	$all = $this->getContentOfAlbum($id, $page, $per_page, $sort, $sortDA);
		        	
		        	$pagina_link = (strpos($link, '?') > 0) ? $link.'&page={page}' : $link.'?page={page}'; 
		        	$tpl->addValue('pagina_link', $pagina_link);
		        	$tpl->addValue('pagina_active', $page);
		        	$tpl->addValue('pagina_count', ceil(($all_images+$f_count)/$per_page));
		        	$tpl->addValue('active_album', $id);
		        	$tpl->addValue('folder_count', $f_count);
		        	$tpl->addValue('image_count', ($folder_count != array()) ? $all_images+$folder_count['image_count'] : '0');	        	
		        	
		        	
		        	if($all != array()){
		        		foreach($all as $content){
		        			$t = new SubViewDescriptor('images');
	
		        			if(get_class($content) == 'GalleryImage'){ // image
	
		        				$t1 = new SubViewDescriptor('images_image');
				        		if($content->getStatus() == self::STATUS_OFFLINE) $t1->showSubView('offline');
				        		$t1->addValue('id', $content->getId());
				        		$t1->addValue('path', $content->getPath());
				        		$t1->addValue('name', $content->getName());
				        		$t1->addValue('short_name', $this->sp->ref('TextFunctions')->cropText($content->getName(), 22));
				        		$t1->addValue('status', $content->getStatus());
				        		$t1->addValue('u_date', $content->getUploadDate());
				        		$t1->addValue('clickAction', str_replace(array('{id}', '{path}'), array($content->getId(), $content->getPath()), $click));

				        		/* --- meta data --- */
				        		$meta = $this->getMetaDataByImage($content->getId());
				        		if($meta != null){
				        			foreach($meta as $m) {
				        				if($this->config['shop']['enable_shop'] && $m->getId() == $this->config['shop']['meta_visible_id'] && $m->getValue() == '1') $t1->showSubView('shop');
				        			}
				        		}
				        		$t->addSubView($t1);
				        		unset($t1);
		        			} else if(get_class($content) == 'GalleryFolder'){ // folder
	
		        				$t2 = new SubViewDescriptor('images_folder');
			        			$t2->addValue('id', $content->getId());
			        			$t2->addValue('name',  $content->getName());
			        			$t2->addValue('datum',  $content->getDate());
			        			$t2->addValue('thumb',  ($content->getThumb() != 0) ? $this->getImage($content->getThumb())->getPath() : '_uploads/'.$this->config['admin']['folder_no_image_path']);
			        			$t2->addValue('count',  $content->getImageCount());
	
			        			if($content->getStatus() == self::STATUS_OFFLINE) $t2->showSubView('offline');
			        			
			        			$t->addSubView($t2);
			        			unset($t2);
		        			}
				        	$tpl->addSubView($t);
		        		}
		        	}
		        	
		        	if(count($all) == 0) $tpl->showSubView('no_images');
		        	
		        	return $tpl->render();
		        	
        		} return 'wrong id';
        		
        	} else return 'no rights';
        }
        /**
         * renders small Folder 
         * not used any more
         * just used for User service 
         * @param $al
         * @param $fo
         * @param $page
         * @param $sort
         * @param $sortDA
         * @param $click
         */
        public function tplSmallFolder($al, $fo, $page, $sort, $sortDA, $click) {
        	$album = $this->getAlbum($al);
        	if($album == null) $album = $this->getAlbum($this->getAlbumId($al));

        	$folder = $this->getFolder($fo);
        	if($folder == null) $folder = $this->getFolderByNameAndAlbum($fo, $album->getId());
        	if($this->checkRight('administer_album', $album->getId())){
        		if($folder != null) { 
	        		$sort = ($sort == -1) ? 'date' : $sort;
	        		$sortDA = ($sortDA == -1) ? 'desc' : $sortDA;
	        		$id = $folder->getId();
	        		
	        		if($id != -1){
		        		$all_images = $this->getImageCountForFolder($id);
			        	$per_page = $this->config['per_page']['small'];
			        	$imgs = (ceil($all_images/$per_page) == 0) ? 1 : ceil($all_images/$per_page);
			        	$page = ($page==-1 || $page > $imgs) ? 1: $page;
			        	
			        	$tpl = new ViewDescriptor($this->config['tpl']['small/folder']);
			        	
			        	$tpl->addValue('pagina_active', $page);
			        	$tpl->addValue('pagina_count', $imgs);
			        	$tpl->addValue('active_album', $id);
			        	$tpl->addValue('image_count', $all_images);
			        	
			        	$images = $this->getImagesByFolder($id, $page, $per_page, $sort, $sortDA);
			        	if($images != array()){
				        	foreach($images as $img){
				        		$t = new SubViewDescriptor('images');
					        	if($img->getStatus() == self::STATUS_OFFLINE) $t->showSubView('offline');
	        	        		$t->addValue('id', $img->getId());
	        	        		$t->addValue('path', $img->getPath());
				        		$t->addValue('name', $img->getName());
				        		$t->addValue('short_name', $this->sp->ref('TextFunctions')->cropText($img->getName(), 22));
				        		$t->addValue('status', $img->getStatus());
				        		$t->addValue('u_date', $img->getUploadDate());
				        		
					        	$t->addValue('clickAction', str_replace(array('{id}', '{path}'), array($img->getId(), $img->getPath()), $click));
	
					        	$tpl->addSubView($t);
				        		unset($t);
				        	}
			        	}
			        	if(count($images) == 0) $tpl->showSubView('no_images');
			        	
			        	return $tpl->render();
		        	} else {
		        		$this->_msg(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
		        		return '';
		        	}
        		} else {
        			$tpl = new ViewDescriptor($this->config['tpl']['small/folder']);
			        	
		        	$tpl->addValue('pagina_active', '1');
		        	$tpl->addValue('pagina_count', '1');
		        	$tpl->addValue('active_album', '1');
		        	
					$tpl->showSubView('no_images');
					
					return $tpl->render();
        		}
        	} else return 'no rights';
        }
        
        /**
         * renders AddOn for Folder
         * @param $al
         * @param $folder
         * @param $page
         * @param $sort
         * @param $sortDA
         * @param $click
         */
        public function addOnFolder($al, $fo, $page, $click, $sort=-1, $sortDA=-1, $view=self::ADDON_VIEW_MATRIX, $reloadFunction='void', $useFunction='void'){
        	if($useFunction == '') $useFunction = 'void';
        	$album = $this->getAlbum($al);
        	if($album == null) $album = $this->getAlbum($this->getAlbumId($al));

        	$folder = $this->getFolder($fo);
        	if($folder == null) $folder = $this->getFolderByNameAndAlbum($fo, $album->getId());
        	if($this->checkRight('administer_album', $album->getId())){
        		if($folder != null) { 
	        		$sort = ($sort == -1) ? 'date' : $sort;
	        		$sortDA = ($sortDA == -1) ? 'desc' : $sortDA;
	        		$id = $folder->getId();
	        		
	        		$folder_main_id = ($folder->getThumb() != null) ? $folder->getThumb()->getId() : -1;
	        		
	        		if($id != -1){
		        		$all_images = $this->getImageCountForFolder($id);
			        	$per_page = $this->config['per_page']['addon'];
			        	$imgs = (ceil($all_images/$per_page) == 0) ? 1 : ceil($all_images/$per_page);
			        	$page = ($page==-1 || $page > $imgs) ? 1: $page;
			        	
			        	$tpl = new ViewDescriptor($this->config['tpl']['addon/folder']);
			        	
			        	$tpl->addValue('pagina_active', $page);
			        	$tpl->addValue('pagina_count', $imgs);
			        	$tpl->addValue('folder_id', $id);
			        	$tpl->addValue('image_count', $all_images);
			        	
			        	$tpl->addValue('reloadFunction', $reloadFunction.'();');

			        	if($useFunction != 'void'){
			        		$tpl->showSubView('use_activated_wo_links');
			        		$tpl->showSubView('use_activated');
			        		$tpl->showSubView('use_activated1');
			        	}
			        	$tpl->addValue('useFunction', $useFunction);
			        	
			        	$images = $this->getImagesByFolder($id, $page, $per_page, $sort, $sortDA);

			        	if($images != array()){
				        	foreach($images as $img){
				        		$t = new SubViewDescriptor('images');
					        	if($img->getStatus() == self::IMG_STATUS_OFFLINE) $t->showSubView('offline');
					        	else if($img->getStatus() == self::IMG_STATUS_MAIN_IMAGE) $t->showSubView('main_image');
					        	
					        	if($img->getId() == $folder_main_id) $t->showSubView('main_image');
					        	
					        	$t->addValue('id', $img->getId());
	        	        		$t->addValue('path', $img->getPath());
				        		$t->addValue('name', $img->getName());
				        		$t->addValue('short_name', $this->sp->ref('TextFunctions')->cropText($img->getName(), 22));
				        		$t->addValue('status', $img->getStatus());
				        		$t->addValue('u_date', $img->getUploadDate());
				        		
					        	$t->addValue('clickAction', str_replace(array('{id}', '{path}'), array($img->getId(), $img->getPath()), $click));
	
					        	$tpl->addSubView($t);
				        		unset($t);
				        	}
			        	}
			        	if(count($images) == 0) $tpl->showSubView('no_images');
			        	
			        	return $tpl->render();
		        	} else {
		        		$this->_msg(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
		        		return '';
		        	}
        		} else {
        			$tpl = new ViewDescriptor($this->config['tpl']['addon/folder']);
			        	
		        	$tpl->addValue('pagina_active', '1');
		        	$tpl->addValue('pagina_count', '1');
		        	$tpl->addValue('active_album', '1');
		        	
					$tpl->showSubView('no_images');
					
					return $tpl->render();
        		}
        	} else return 'no rights';
        }
        
        public function addOnUpload($al, $fo, $link){
        	$album = $this->getAlbum($al);
        	if($album == null) $album = $this->getAlbum($this->getAlbumId($al));

        	if($album != null){
	        	$folder = $this->getFolder($fo);
	        	if($folder == null) $folder = $this->getFolderByNameAndAlbum($fo, $album->getId());
	        	
	        	if($this->checkRight('administer_album', $album->getId())){
	        		//$this->debugVar($fo);
	        		if($folder != null || $fo == 'new'){
		        		$tpl = new ViewDescriptor($this->config['tpl']['addon/upload']);
		        		
		        		$tpl->addValue('album_id', $album->getId());
		        		$tpl->addValue('folder_id', $fo=='new'? 'new' : $folder->getId());
		        		$tpl->addValue('link', $link);
	        			$tpl->addValue('types', $this->config['valid_file_types']);
	        			$tpl->addValue('js_file_admin', $this->config['js_file_admin']);
	        			$tpl->addValue('max_file_size', $this->config['max_file_size']);
	        			$tpl->addValue('max_uploads', $this->config['max_uploads']);
		        		
		        		return $tpl->render();
	        		} else {
	        			$this->_msg($this->_('_wrong id'), Messages::ERROR);
	        			return '';
	        		}
	        	} else return 'no rights';
        	} else {
        		$this->_msg($this->_('_wrong id'), Messages::ERROR);
        		return '';
        	}
        }
        
        public function addOnWysiwygFolder($al, $fo, $page, $sort=-1, $sortDA=-1, $view=self::ADDON_VIEW_MATRIX, $reloadFunction='void', $useFunction='void'){
        	if($useFunction == '') $useFunction = 'void';
        	$album = $this->getAlbum($al);
        	if($album == null) $album = $this->getAlbum($this->getAlbumId($al));

        	$folder = $this->getFolder($fo);
        	if($folder == null) $folder = $this->getFolderByNameAndAlbum($fo, $album->getId());
        	if($this->checkRight('administer_album', $album->getId())){
        		if($folder != null) { 
	        		$sort = ($sort == -1) ? 'date' : $sort;
	        		$sortDA = ($sortDA == -1) ? 'desc' : $sortDA;
	        		$id = $folder->getId();
	        		
	        		$folder_main_id = ($folder->getThumb() != null) ? $folder->getThumb()->getId() : -1;
	        		
	        		if($id != -1){
		        		$all_images = $this->getImageCountForFolder($id);
			        	$per_page = $this->config['per_page']['wysiwyg'];
			        	$imgs = (ceil($all_images/$per_page) == 0) ? 1 : ceil($all_images/$per_page);
			        	$page = ($page==-1 || $page > $imgs) ? 1: $page;
			        	
			        	$tpl = new ViewDescriptor($this->config['tpl']['wysiwyg/folder']);
			        	
			        	$tpl->addValue('pagina_active', $page);
			        	$tpl->addValue('pagina_count', $imgs);
			        	$tpl->addValue('folder_id', $id);
			        	$tpl->addValue('album_id', $album->getId());
			        	$tpl->addValue('image_count', $all_images);
			        	
			        	$tpl->addValue('reloadFunction', $reloadFunction.'();');
			        	if($useFunction != 'void'){
			        		$tpl->showSubView('use_activated_wo_links');
			        		$tpl->showSubView('use_activated');
			        	}
			        	$tpl->addValue('useFunction', $useFunction);
			        	
			        	$images = $this->getImagesByFolder($id, $page, $per_page, $sort, $sortDA);

			        	if($images != array()){
				        	foreach($images as $img){
				        		$t = new SubViewDescriptor('images');
					        	if($img->getStatus() == self::IMG_STATUS_OFFLINE) $t->showSubView('offline');
					        	else if($img->getStatus() == self::IMG_STATUS_MAIN_IMAGE) $t->showSubView('main_image');
					        	
					        	if($img->getId() == $folder_main_id) $t->showSubView('main_image');
					        	
					        	$t->addValue('id', $img->getId());
	        	        		$t->addValue('path', $img->getPath());
				        		$t->addValue('name', $img->getName());
				        		$t->addValue('short_name', $this->sp->ref('TextFunctions')->cropText($img->getName(), 22));
				        		$t->addValue('status', $img->getStatus());
				        		$t->addValue('u_date', $img->getUploadDate());
				        		
					        	$tpl->addSubView($t);
				        		unset($t);
				        	}
			        	}
			        	if(count($images) == 0) $tpl->showSubView('no_images');
			        	
			        	return $tpl->render();
		        	} else {
		        		$this->_msg(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core')), Messages::RUNTIME_ERROR);
		        		return '';
		        	}
        		} else {
        			$this->_msg($this->_('_Folder not found'), Messages::ERROR);
					return '';
        		}
        	} else return 'no rights';
        }
        
        public function addOnWysiwygUpload($al, $fo, $link){
        	$album = $this->getAlbum($al);
        	if($album == null) $album = $this->getAlbum($this->getAlbumId($al));

        	if($album != null){
	        	$folder = $this->getFolder($fo);
	        	if($folder == null) $folder = $this->getFolderByNameAndAlbum($fo, $album->getId());
	        	
	        	if($this->checkRight('administer_album', $album->getId())){
	        		//$this->debugVar($fo);
	        		if($folder != null || $fo == 'new'){
		        		$tpl = new ViewDescriptor($this->config['tpl']['wysiwyg/upload']);
		        		
		        		$tpl->addValue('album_id', $album->getId());
		        		$tpl->addValue('folder_id', $fo=='new'? 'new' : $folder->getId());
		        		$tpl->addValue('link', $link);
	        			$tpl->addValue('types', $this->config['valid_file_types']);
	        			$tpl->addValue('js_file_admin', $this->config['js_file_admin']);
	        			$tpl->addValue('max_file_size', $this->config['max_file_size']);
	        			$tpl->addValue('max_uploads', $this->config['max_uploads']);
		        		
		        		return $tpl->render();
	        		} else {
	        			$this->_msg($this->_('_wrong id'), Messages::ERROR);
	        			return '';
	        		}
	        	} else return 'no rights';
        	} else {
        		$this->_msg($this->_('_wrong id'), Messages::ERROR);
        		return '';
        	}
        }
        
        public function getNoImagePath() {  return '_template/'. $GLOBALS['tpl']['activeTemplate'].'/'.$GLOBALS['Localization']['language'].$this->config['no_image_path']; }
    }
?>