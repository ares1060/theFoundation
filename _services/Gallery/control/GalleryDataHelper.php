<?php
	
	class GalleryDataHelper extends TFCoreFunctions{
		protected $name = 'Gallery';
		
		const STATUS_HIDDEN = 0;
		const STATUS_ONLINE = 1;
		const STATUS_OFFLINE = 2;
		const STATUS_SYSTEM = 3;
		
		/**
		 * constructor requires settings
		 * @param unknown_type $settings
		 */
		function __construct($settings){
			parent::__construct();
			$this->settings = $settings;
		}	
		
		/* ============================================  Folders */
		/**
		 * returnes folders 
		 * there is no pagination
		 * @param unknown_type $status
		 */
		public function getFolders($status=array(), $parent=0, $sort='default') {

			// create user, status and category string 
			$user = 'WHERE `u_id`="'.mysql_real_escape_string($this->sp->ref('User')->getViewingUser()->getId()).'"';
			$album = ' AND `parent`="'.mysql_real_escape_string($parent).'"';
			$statusAR = array();
			foreach($status as $s){
				$statusAR[] = '`status`="'.mysql_real_escape_string($s).'"';
			}
			if($statusAR != array()) $status = 'AND ('.implode(' OR ', $statusAR).')';
			
			// create sort string
			switch($sort){
				case 'date' || 'date ASC':
					$sort = ' ORDER BY c_date ASC';
					break;
				case 'date DESC':
					$sort = ' ORDER BY c_date DESC';
					break;
				case 'name' || 'name ASC';
					$sort = ' ORDER BY name ASC';
					break;
				case 'name DESC':
					$sort = ' ORDER BY name DESC';
					break;
				default:
					$sort = ' ORDER BY f_id DESC';
					break;
			}

        	$pp = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'gallery_folder` '.$user.$album.$status.$sort);

        	if($pp != ''){
				$return = array();
				foreach($pp as $p){
					$return[] = new GalleryFolder($p['f_id'], $p['parent'], $p['u_id'], $p['name'], $p['c_date'], $p['root'], $p['status']);
				}
				
				return $return;
			} else return null;
		}
		
		/**
		 * returnes folder by id
		 * @param unknown_type $id
		 * @return NULL
		 */
		public function getFolderById($id) {
			if($id > -1) {
				$p = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'gallery_folder` WHERE f_id="'.mysql_real_escape_string($id).'"');
				
				if($p != '') {
					return new GalleryFolder($p['f_id'], $p['parent'], $p['u_id'], $p['name'], $p['c_date'], $p['root'], $p['status']);
				} else return null;
			} else return null;
		}
		
		/**
		 * creates Album
		 * @param unknown_type $name
		 * @param unknown_type $status
		 */
		public function createAlbum($name, $status=GalleryDataHelper::STATUS_ONLINE){
			return $this->createFolder(0, $name, $status);
		}
		
		/**
		 * creates Folder
		 * @param unknown_type $parent
		 * @param unknown_type $name
		 * @param unknown_type $status
		 */
		public function createFolder($parent, $name, $status=GalleryDataHelper::STATUS_ONLINE){
           	// if album to create -> create dummy folder
           	if($parent == 0) {
           		$parent = new GalleryFolder(0, 0, 0, '', 0, 0, self::STATUS_ONLINE);
           		$album = true;
           	} else $album = false;
			if(!is_object($parent) || get_class($parent) != 'GalleryFolder') $parent = $this->getFolderById($parent);
			
			// check rights
			if(($album && $this->checkRight('administerAlbum')) || (!$album && $this->checkRight('administerFolder', $parent->getId()))){
				// check data
				if($parent != null && $name != ''){
					$root = ($parent->getParentFolderId() == 0) ? $parent->getId() : $parent->getRoot();
					$p = $this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'gallery_folder` 
													(`root`, `parent`, `u_id`, `name`, `c_date`, `status`) VALUES
													("'.mysql_real_escape_string($root).'",
													 "'.mysql_real_escape_string($parent->getId()).'",	
													 "'.mysql_real_escape_string($this->sp->ref('User')->getViewingUser()->getId()).'",	
													 "'.mysql_real_escape_string($name).'",	
													 "'.time().'", "'.$status.'")');
					if($p !== false){
						
						$this->sp->ref('Rights')->authorizeUser('Gallery', 'administerFolder', $this->sp->ref('User')->getViewingUser()->getId(), $p);
						
						if($album) $this->_msg($this->_('_Album created successfully', 'gallery'), Messages::INFO);
						else $this->_msg($this->_('_Folder created successfully', 'gallery'), Messages::INFO);
						return true;
					} else {
						$this->_msg($this->_('_Error creating Folder', 'gallery'), Messages::ERROR);
						return false;
					}
				} else {
					$this->_msg($this->_('_Error creating Folder', 'gallery'), Messages::ERROR);
					return false;
				}
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
				return false;
			}
		}
		
		/**
		 * deletes Folder, Subfolders and Images
		 * @param unknown_type $folder
		 */
		public function deleteFolder($folder, $blockMsg=false) {
			if(!is_object($folder) || get_class($folder) != 'GalleryFolder') $folder = $this->getFolderById($folder);
			
			if($folder != null) {		
				if((($folder->getParentFolderId() == 0 && $this->checkRight('administerAlbum')) ||
					($folder->getParentFolderId() > 0 && $this->checkRight('administerFolder', $folder->getId())))){
					
					$subfolder = $this->getFolders(self::getAllStatus(), $folder->getId());
					
					$error = false;
	
					if(count($subfolder > 0)){
						foreach ($subfolder as $sf){
							// recursively delete subfolders and supress msgs
							$error = !(!$error && $this->deleteFolder($sf, true));
						}
					}
	
					if(!$error){
						// delete Images
						$this->deleteImagesInFolder($folder);
						
						// delete Folder
						$q = $this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'gallery_folder` WHERE f_id ="'.mysql_real_escape_string($folder->getId()).'"');
						
						if($q){
							// remove rights
							$this->sp->ref('Rights')->unauthorizeUser('Gallery', 'administerFolder', $this->sp->ref('User')->getViewingUser()->getId(), $folder->getId());
							
							if(!$blockMsg) $this->_msg($this->_('_Folder deleted successfully', 'gallery'), Messages::INFO);
							return true;
						} else {
							if(!$blockMsg) $this->_msg($this->_('_Could not delete Folder', 'gallery'), Messages::ERROR);
							return false;
						}
					} else {
						if(!$blockMsg) $this->_msg($this->_('_Could not delete Folder', 'gallery'), Messages::ERROR);
						return false;
					}
				} else {
					if(!$blockMsg) $this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
					return false;
				}
			} else {
				error_log('TF:Gallery: wrong id'.$folder);
				return false;
			}
		}
		
		/**
		 * edits folder and sets new name or status
		 * @param unknown_type $folder
		 * @param unknown_type $name
		 * @param unknown_type $status
		 */
		public function editFolder($folder, $name='', $status=-1){
			if(!is_object($folder) || get_class($folder) != 'GalleryFolder') $folder = $this->getFolderById($folder);
			
			if($folder != null){
				if($name != $folder->getName() || $status != $folder->getStatus()){
					//calculate strings
					$name = ($name != '') ? ' `name`="'.mysql_real_escape_string($name).'" ' : '';
					$status = ($status != -1 && self::validStatus($status)) ? ' `status`="'.mysql_real_escape_string($status).'" ' : '';
					
					$q = $this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'gallery_folder` SET '.$name.' '.$status.' WHERE `f_id`="'.mysql_real_escape_string($folder->getId()).'"');
					
					if($q) {
						$this->_msg($this->_('_Folder edited successfully', 'gallery'), Messages::INFO);
						return true;
					} else {
						$this->_msg($this->_('_Could not edit Folder', 'gallery'), Messages::ERROR);
						return false;
					}
				} else {
					$this->_msg($this->_('_Folder edited successfully', 'gallery'), Messages::INFO);
					return true;
				}
			} else {
				$this->debugVar('asdf');
				return false;
			}
		}
		
		/* ============================================   images */
		/**
		 * returnes image by id
		 * @param unknown_type $id
		 */
		public function getImageById($id){
			
			$p = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'gallery_image` WHERE
					i_id = "'.mysql_real_escape_string($id).'" ');
			
			if($p != null && $p != array()){
				return new GalleryImage($p['i_id'], $p['name'], $p['path'], $p['hash'], $p['status'], $p['u_id'], $p['c_date'], $p['size'], $p['s_date']);
			} else return null;
		}
		/** 
		 * returnes list of all images by folder id
		 * @param unknown_type $folderId
		 * @param unknown_type $page
		 * @param unknown_type $status
		 */
		public function getImagesByFolder($folder, $page=-1, $status=array()){
			if(!is_object($folder) || get_class($folder) != 'GalleryFolder') $folder = $this->getFolderById($folder);
			
			if($folder != null) {
				// get count of images
				$count = $this->getImageCountByFolder($folder, $status);
				
				// create limit string
				$per_page = $this->_setting('admin.per_page.images');
				$limit = ($page == -1) ? '' : ' LIMIT '.(mysql_real_escape_string($page-1)*mysql_real_escape_string($per_page)).', '.mysql_real_escape_string($per_page).';';
				// create user, status and category string
				$user = 'AND `u_id`="'.mysql_real_escape_string($this->sp->ref('User')->getViewingUser()->getId()).'"';
				
				//create status string
				$statusAR = array();
				foreach($status as $s){
					$statusAR[] = 'gif.status="'.mysql_real_escape_string($s).'"';
				}
				if($statusAR != array()) $status = 'AND ('.implode(' OR ', $statusAR).')';
				else $status = '';
				
				$pp = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'gallery_image_folder` gif LEFT JOIN
						`'.$GLOBALS['db']['db_prefix'].'gallery_image` gi ON gi.i_id = gif.i_id WHERE
						gif.f_id = "'.mysql_real_escape_string($folder->getId()).'" '.$user.$status.$limit);
				
				if($pp != ''){
					$return = array();
					foreach($pp as $p){
						$return[] = new GalleryImage($p['i_id'], $p['name'], $p['path'], $p['hash'], $p['status'], $p['u_id'], $p['c_date'], $p['size'], $p['s_date']);
					}
					return $return;
				} else return null;
			} else return null;
		}
		
		/**
		 * returnes image count by folder
		 * @param unknown_type $folderId
		 * @param unknown_type $status
		 */
		public function getImageCountByFolder($folder, $status) {
			if(!is_object($folder) || get_class($folder) != 'GalleryFolder') $folder = $this->getFolderById($folder);
			
			if($folder != null) {
				//create status string
				$statusAR = array();
				foreach($status as $s){
					$statusAR[] = 'gif.status="'.mysql_real_escape_string($s).'"';
				}
				if($statusAR != array()) $status = 'AND ('.implode(' OR ', $statusAR).')';
				else $status = '';
				
				// create user, status and category string
				$user = ' AND `u_id`="'.mysql_real_escape_string($this->sp->ref('User')->getViewingUser()->getId()).'"';
	
				$pp = $this->mysqlRow('SELECT COUNT(*) count FROM `'.$GLOBALS['db']['db_prefix'].'gallery_image_folder` gif LEFT JOIN
															`'.$GLOBALS['db']['db_prefix'].'gallery_image` gi ON gi.i_id = gif.i_id WHERE 
															gif.f_id = "'.mysql_real_escape_string($folder->getId()).'" '.$user.$status);
				
				if($pp != array()){
					return $pp['count'];
				} else return 0;
			} else return -1;
		}
		
		/**
		 * returnes folder count of given image
		 * @param unknown_type $image
		 */
		public function getFolderCountByImage($image){
			if(!is_object($image) || get_class($image) != 'GalleryImage') $image = $this->getImageById($image);
			
			if($image != null) {
				$pp = $this->mysqlRow('SELECT COUNT(*) count FROM `'.$GLOBALS['db']['db_prefix'].'gallery_image_folder` WHERE
						i_id = "'.mysql_real_escape_string($image->getId()).'" ');
				
				if($pp != array()){
					return $pp['count'];
				} else return 0;
				
			} else return -1;
		}
		
		/**
		 * deletes images in specified folder
		 * @param unknown_type $folder
		 */
		private function deleteImagesInFolder($folder){
			if(!is_object($folder) || get_class($folder) != 'GalleryFolder') $folder = $this->getFolderById($folder);
			
			if($folder != null){
				$images = $this->getImagesByFolder($folder, -1, self::getAllStatus());
				
				$hasRights = true;
				
				foreach($images as $img){
					if($this->checkRights('administerImages', $img->getId()) && $hasRights) {
						$count = $this->getFolderCountByImage($img);
						
						// if image is just in this folder you can delete file as well
						if($count == 1) {
							// delete file
							if(is_file($GLOBALS['config']['root'].$img->getPath())) unlink($GLOBALS['config']['root'].$img->getPath());
							// delete cache
							$this->sp->ref('Image')->clearCache($img->getPath());
						}
					} else {
						$hasRights = false;
						break;
					}
				}
				
				$q = $this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'gallery_image_folder` WHERE f_id ="'.mysql_real_escape_string($folder->getId()).'"');
				
				if($q){
// 					$this->_msg($this->_('_Folder deleted successfully', 'gallery'), Messages::INFO);
					return true;
				} else {
// 					$this->_msg($this->_('_Could not delete Folder', 'gallery'), Messages::ERROR);
					return false;
				}
			}
		}
		
		/* ============================================   util */
		/**
		 * returnes if status is a valid status id
		 * @param unknown_type $status
		 */
		public static function validStatus($status) {
			return $status == self::STATUS_ONLINE || $status == self::STATUS_HIDDEN || $status == self::STATUS_OFFLINE || $status == self::STATUS_SYSTEM;
		}
		/**
		 * returnes array of all available status
		 */
		public static function getAllStatus() {
			return array(self::STATUS_HIDDEN, self::STATUS_OFFLINE, self::STATUS_ONLINE, self::STATUS_SYSTEM);
		}
	}
?>