<?php
	
	class GalleryDataHelper extends TFCoreFunctions{
		protected $name = 'Gallery';
		
		// consts now are at the Gallery class -> for backward compatibility status constants will be duplicated
		const STATUS_HIDDEN = Gallery::STATUS_HIDDEN;
		const STATUS_ONLINE = Gallery::STATUS_ONLINE;
		const STATUS_OFFLINE = Gallery::STATUS_OFFLINE;
		const STATUS_SYSTEM = Gallery::STATUS_SYSTEM;
		
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
		public function getFolders($status=array(), $parent=0, $sort='default', $user=-1) {

			if($user == -1){
				$userid = $this->sp->ref('User')->getViewingUser()->getId();
			} else {
				$u = $this->sp->ref('User')->getUserById($user);
				$userid = ($u != null) ? $u->getId() : $this->sp->ref('User')->getViewingUser()->getId();
			}
			
			// create user, status and category string 
			$user = 'WHERE `u_id`="'.mysql_real_escape_string($userid).'"';
			$album = ' AND `parent`="'.mysql_real_escape_string($parent).'"';
			$statusAR = array();
			foreach($status as $s){
				$statusAR[] = '`status`="'.mysql_real_escape_string($s).'"';
			}
			if($statusAR != array()) $status = 'AND ('.implode(' OR ', $statusAR).')';
			
			// create sort string
			switch($sort){
				case 'date': case 'date ASC':
					$sort = ' ORDER BY c_date ASC';
					break;
				case 'date DESC':
					$sort = ' ORDER BY c_date DESC';
					break;
				case 'name': case 'name ASC';
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
		 * returnes array of folder ids given image is linked to
		 * @param GalleryImage or int $image - could handle ImageObject or image id
		 * @param unknown_type $justIds
		 */
		public function getFolderIdsByImage($image, $justIds=true) {
			if(!is_object($image) || get_class($image) != 'GalleryImage') $image = $this->getFolderById($image);

			if($image != null) {
				$p = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'gallery_image_folder` WHERE i_id="'.mysql_real_escape_string($image->getId()).'"');
				
				$return = array();
				
				if($p != '') {
					foreach($p as $folder){
						$return[] = $folder['f_id']; 
					}
				} 
				
				return $return;
			}
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
		 * wrapper function for getSubFolderByName
		 * @see GalleryDataHelper->getSubFolderByName
		 * @param unknown_type $album
		 * @param unknown_type $name
		 */
		public function getFolderByAlbumAndName($album, $name){
			return $this->getSubFolderByName($album, $name);
		}
		
		/**
		 * returnes Folder by parent id and name
		 * @param unknown_type $parent
		 * @param unknown_type $name
		 */
		public function getFolderByParentAndName($parent, $name) {
			if(!is_object($parent) || get_class($parent) != 'GalleryFolder') $parent = $this->getFolderById($parent);
			
			
		}
		
		/**
		 * returnes sub Folder of album by given name
		 * if more than one folders have the same name the first one will be returned
		 * @param unknown_type $album
		 * @param unknown_type $subfolder_name
		 */
		public function getSubFolderByName($album, $subfolder_name){
			if(!is_object($album) || get_class($album) != 'GalleryFolder') $album = $this->getFolderById($album);
			if($album != null){
				$p = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'gallery_folder` 
												WHERE `parent`="'.mysql_real_escape_string($album->getId()).'" 
												AND `name`="'.mysql_real_escape_string($subfolder_name).'"');
// 												AND `u_id`="'.mysql_real_escape_string($this->sp->ref('User')->getViewingUser()->getId()).'"');
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
		 * @param boolean register ||Êis a right-system hack to allow everyone to create a folder
		 */
		public function createFolder($parent, $name, $status=GalleryDataHelper::STATUS_ONLINE, $silent=false){
           	// if album to create -> create dummy folder
           	if($parent == 0) {
           		$parent = new GalleryFolder(0, 0, 0, '', 0, 0, self::STATUS_ONLINE);
           		$album = true;
           	} else $album = false;
			if(!is_object($parent) || get_class($parent) != 'GalleryFolder') $parent = $this->getFolderById($parent);
			
			// check rights
			if(($album && $this->checkRight('administerAlbum')) || // if album you have to be autorized to create Folder
				(!$album && (
						($this->checkRight('administerFolder', $parent->getId()) // if no album you have to authorized to edit parent
								&& $this->checkRight('administerFolder') // ....  and create a folder at all
						)) || 
						$parent->getStatus() == GalleryDataHelper::STATUS_SYSTEM)){  // or if folder is a System folder you can create it
				
				// register is a right-system hack to allow everyone to create a folder
				
				// check data
				if($parent != null && $name != ''){
					$root = ($parent->getParentFolderId() == 0) ? $parent->getId() : $parent->getRoot();
					$viewing_user = $this->sp->ref('User')->getViewingUser();
					$p = $this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'gallery_folder` 
													(`root`, `parent`, `u_id`, `name`, `c_date`, `status`) VALUES
													("'.mysql_real_escape_string($root).'",
													 "'.mysql_real_escape_string($parent->getId()).'",	
													 "'.mysql_real_escape_string((isset($viewing_user)) ? $viewing_user->getId() : '-1').'",	
													 "'.mysql_real_escape_string($name).'",	
													 "'.time().'", "'.$status.'")');
					if($p !== false){
						
						if(isset($viewing_user)) $this->sp->ref('Rights')->authorizeUser('Gallery', 'administerFolder', $this->sp->ref('User')->getViewingUser()->getId(), $p);
						
						if(!$silent) {
							if($album) $this->_msg($this->_('_Album created successfully', 'gallery'), Messages::INFO);
							else $this->_msg($this->_('_Folder created successfully', 'gallery'), Messages::INFO);
						}
						
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
					($folder->getParentFolderId() > 0 && $this->checkRight('administerFolder', $folder->getId())) ||
					$folder->getStatus() == Gallery::STATUS_SYSTEM)){
					
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
			
			if($folder != null &&  // there has to be a folder
					$this->checkRight('administerFolder', $folder->getId()) &&  //you have to be authorized to edit folder
					($folder->getStatus() != Gallery::STATUS_SYSTEM || $this->sp->ref('User')->getLoggedInUser()->getGroup() == 1)){ // if status = Systemfolder you have to be root (hardcoded)
				
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
// 				$this->debugVar('asdf');
				return false;
			}
		}
		
		/* ============================================   images */
		/**
		 * returnes image by id
		 * @param unknown_type $id
		 */
		public function getImageById($id, $withFolders = false){
			
			$p = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'gallery_image` WHERE
					i_id = "'.mysql_real_escape_string($id).'" ');
			
			if($p != null && $p != array()){
				$tmp = new GalleryImage($p['i_id'], $p['name'], $p['path'], $p['hash'], $p['u_id'], $p['c_date'], $p['size'], $p['s_date']);
				
				if($withFolders){
					foreach($this->getFolderIdsByImage($tmp) as $folderId){
						$tmp->addFolderId($folderId);
					}
				}
				
				return $tmp;
			} else return null;
		}
		/** 
		 * returnes list of all images by folder id
		 * @param unknown_type $folderId
		 * @param unknown_type $page
		 * @param unknown_type $status
		 */
		public function getImagesByFolder($folder, $page=-1, $per_page, $status=array()){
			if(!is_object($folder) || get_class($folder) != 'GalleryFolder') $folder = $this->getFolderById($folder);
			
			if($folder != null) {
				// get count of images
				$count = $this->getImageCountByFolder($folder, $status);
				
				// create limit string
// 				$per_page = $this->_setting('admin.per_page.images');
				$limit = ($page == -1) ? '' : ' LIMIT '.(mysql_real_escape_string($page-1)*mysql_real_escape_string($per_page)).', '.mysql_real_escape_string($per_page).';';
				// create user, status and category string
				$user = '';//'AND `u_id`="'.mysql_real_escape_string($this->sp->ref('User')->getViewingUser()->getId()).'"';
				
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
		
		public function getSurroundingImagesByImageAndFolder($image, $folder, $sort='default'){
			if(!is_object($image) || get_class($image) != 'GalleryImage') $image = $this->getImageById($image);
			if(!is_object($folder) || get_class($folder) != 'GalleryFolder') $folder = $this->getFolderById($folder);

			if($image != null && $folder != null){
				// create sort and < > strings
				switch($sort){
					case 'upload_date': case 'upload_date ASC':
						$sort = ' ORDER BY i.c_date ASC';
						$look_prev = 'i.c_date < "'.mysql_real_escape_string($image->getCreationDate()).'"';
						$look_next = 'i.c_date > "'.mysql_real_escape_string($image->getCreationDate()).'"';
						break;
					case 'upload_date DESC':
						$sort_prev = ' ORDER BY i.c_date DESC';
						$sort_next = ' ORDER BY i.c_date ASC';
						$look_prev = 'i.c_date > "'.mysql_real_escape_string($image->getCreationDate()).'"';
						$look_next = 'i.c_date < "'.mysql_real_escape_string($image->getCreationDate()).'"';
						break;
					case 'shot_date': case 'shot_date ASC';
						$sort_prev = ' ORDER BY i.s_date ASC';
						$sort_next = ' ORDER BY i.s_date DESC';
						$look_prev = 'i.s_date < "'.mysql_real_escape_string($image->getShotDate()).'"';
						$look_next = 'i.s_date > "'.mysql_real_escape_string($image->getShotDate()).'"';
						break;
					case 'shot_date DESC';
						$sort_prev = ' ORDER BY i.s_date DESC';
						$sort_next = ' ORDER BY i.s_date ASC';
						$look_prev = 'i.s_date > "'.mysql_real_escape_string($image->getShotDate()).'"';
						$look_next = 'i.s_date < "'.mysql_real_escape_string($image->getShotDate()).'"';
						break;
					case 'size ASC':
						$sort_prev = ' ORDER BY i.size ASC';
						$sort_next = ' ORDER BY i.size DESC';
						$look_prev = 'i.size < "'.mysql_real_escape_string($image->getSize()).'"';
						$look_next = 'i.size > "'.mysql_real_escape_string($image->getSize()).'"';
						break;
					case 'size DESC':
						$sort_prev = ' ORDER BY i.size DESC';
						$sort_next = ' ORDER BY i.size ASC';
						$look_prev = 'i.size > "'.mysql_real_escape_string($image->getSize()).'"';
						$look_next = 'i.size < "'.mysql_real_escape_string($image->getSize()).'"';
						break;
					default:
						$sort_prev = ' ORDER BY i.i_id DESC';
						$sort_next = ' ORDER BY i.i_id ASC';
						$look_prev = 'i.i_id < "'.mysql_real_escape_string($image->getId()).'"';
						$look_next = 'i.i_id > "'.mysql_real_escape_string($image->getId()).'"';
						break;
				}
				
				$r = array();
				
				// get previous
				$p = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'gallery_image_folder` ifo
													LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'gallery_image` i ON ifo.i_id = i.i_id
													WHERE ifo.f_id="'.mysql_real_escape_string($folder->getId()).'" AND
															'.$look_prev.' '.$sort_prev.' LIMIT 1');

				if($p != null && $p != array()) {
					$r['prev'] = new GalleryImage($p['i_id'], $p['name'], $p['path'], $p['hash'], $p['u_id'], $p['c_date'], $p['size'], $p['s_date']);
				} else $r['prev'] = null;
				
				// get next
				$p = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'gallery_image_folder` ifo
						LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'gallery_image` i ON ifo.i_id = i.i_id
						WHERE ifo.f_id="'.mysql_real_escape_string($folder->getId()).'" AND
						'.$look_next.' '.$sort_next.' LIMIT 1');

				if($p != null && $p != array()) {
					$r['next'] = new GalleryImage($p['i_id'], $p['name'], $p['path'], $p['hash'], $p['u_id'], $p['c_date'], $p['size'], $p['s_date']);
				} else $r['next'] = null;
				
				return $r;
			} else return null;
		}
		
		/**
		 * checks if images is in Folder $folder
		 * @param unknown_type $image
		 * @param unknown_type $folder_id
		 */
		public function checkImageInFolder($image, $folder) {
			if(!is_object($image) || get_class($image) != 'GalleryImage') $image = $this->getImageById($image);
			if(!is_object($folder) || get_class($folder) != 'GalleryFolder') $folder = $this->getFolderById($folder);
		
			if($image != null && $folder != null){
				return in_array($folder->getId(), $this->getFoldersIdsByImage());
			} else return false;
		}
		
		/**
		 * returnes image count by folder
		 * @param unknown_type $folderId
		 * @param array() $status
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
				$user = ''; //' AND `u_id`="'.mysql_real_escape_string($this->sp->ref('User')->getViewingUser()->getId()).'"';
	
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
		private function deleteImagesInFolder($folder, $imageIds=array()){
			if(!is_object($folder) || get_class($folder) != 'GalleryFolder') $folder = $this->getFolderById($folder);
			
			if($folder != null){
				$images = $this->getImagesByFolder($folder, -1, self::getAllStatus());
// 				error_log($images);
				$hasRights = true;
				$q = $qI = true;
				foreach($images as $img){
					if(in_array($img->getId(), $imageIds) || $imageIds == array()){
						if(($this->checkRight('administerImages', $img->getId()) || 
							$this->checkRight('administerFolder', $folder->getId()) && $hasRights) ||
							$folder->getStatus() == Gallery::STATUS_SYSTEM) {
							$count = $this->getFolderCountByImage($img);
							
							// delete connection to folder
							$q = $q && $this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'gallery_image_folder` 
															WHERE f_id ="'.mysql_real_escape_string($folder->getId()).'" 
															AND i_id="'.mysql_real_escape_string($img->getId()).'"');
							
							// if image is just in this folder you can delete file, cache and database entry as well
							if($count == 1) {
								// delete file
								if(is_file($GLOBALS['config']['root'].$img->getPath())) unlink($GLOBALS['config']['root'].$img->getPath());
								
								// delete cache
								$this->sp->ref('Image')->clearCache($img->getPath());
								
								// delete database entry
								$qI = $qI && $this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'gallery_image`
										WHERE i_id="'.mysql_real_escape_string($img->getId()).'"');
							}
						} else {
							$hasRights = false;
							break;
						}
					}
				}
				
				if($q){
// 					$this->_msg($this->_('_Folder deleted successfully', 'gallery'), Messages::INFO);
					return true;
				} else {
// 					$this->_msg($this->_('_Could not delete Folder', 'gallery'), Messages::ERROR);
					return false;
				}
			}
		}
		
		public function deleteImageFromFolder($folder, $image) {
			if(!is_object($folder) || get_class($folder) != 'GalleryImage') return $this->deleteImagesInFolder($folder, array($image));
			else $this->deleteImagesInFolder($folder, array($image->getId()));
		}

		/**
		 * create Image in database
		 * @param unknown_type $name
		 * @param unknown_type $path
		 * @param unknown_type $shot_date
		 */
		public function createImage($name, $path, $shot_date){
			if(is_file($GLOBALS['config']['root'].$path)){
// 				$this->debugVar($shot_date);
				//TODO: real shot date
				return ($this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'gallery_image
											(name, path, hash, u_id, c_date, s_date, size) VALUES
											("'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($name)).'",
											 "'.mysql_real_escape_string($path).'",
											 "'.md5_file($GLOBALS['config']['root'].$path).'",
											 "'.mysql_real_escape_string($this->sp->ref('User')->getViewingUser()->getId()).'",
											 "'.microtime().'",
											 "'.mysql_real_escape_string($shot_date).'",
											 "'.filesize($GLOBALS['config']['root'].$path).'")'));
			} else return -1;
		}
		
		/**
		 * adds Image to given folder
		 * Image and Folder have to exists in database
		 * @param unknown_type $image
		 * @param unknown_type $folder
		 */
		public function addImageToFolder($image, $folder) {
			if(!is_object($folder) || get_class($folder) != 'GalleryFolder') $folder = $this->getFolderById($folder);
			if(!is_object($image) || get_class($image) != 'GalleryImage') $image = $this->getImageById($image);
				
			if($folder != null && $image != null){
				return $this->mysqlInsert('INSERT INTO '.$GLOBALS['db']['db_prefix'].'gallery_image_folder
											(i_id, f_id, status) VALUES
											("'.mysql_real_escape_string($image->getId()).'",
											 "'.mysql_real_escape_string($folder->getId()).'",
											 "'.self::STATUS_ONLINE.'");');
			}
		}
		
		/**
		 * Uploads Images to Folder
		 * moves Images to final destination and adds files database
		 * @param unknown_type $images
		 * @param unknown_type $folder
		 */
		public function uploadImages($images, $folder) {
			if(!is_object($folder) || get_class($folder) != 'GalleryFolder') $folder = $this->getFolderById($folder);
			
			if($folder != null){
				if($images != array()) {
					$return = array();
					$success = 0;
					foreach($images as $img){
						if($img['size'] <= $this->_setting('upload.max_file_size')){ 										// check size again
	        				if(preg_match("/\." . $this->_setting('upload.valid_file_types') . "$/i", $img['name'])){	// check types
	        					if(is_dir($GLOBALS['to_root'].$this->_setting('upload.upload_dir'))){									// check if upload dir exists
	        									
	        						$exts = explode('.', strtolower($img['name']));
	        									
	        						$newfilepath = $this->_setting('upload.upload_dir').$this->_setting('upload.upload_prefix').str_replace(array('.', ' '), array('', ''), microtime()).'.'.$exts[count($exts)-1];
	        									
	        						if(copy($img['tmp_name'], $GLOBALS['to_root'].$newfilepath)){ // moving to final destinition (copy instead of move_uploaded_file)
	        							unlink($img['tmp_name']);
	        							
	        							//exifdata
	        							$exif = new phpExifReader($GLOBALS['to_root'].$newfilepath);
	        							//print_r($exif->getImageInfo());
	        							$exif = $exif->getImageInfo();
	        										
	        							$shot_date = (isset($exif['dateTimeDigitized'])) ?  $exif['dateTimeDigitized'] :'';
	
	        							$newid = $this->createImage($img['name'], $newfilepath, $shot_date);
	
	        							if($newid != -1 && $newid != false) {//save to database	
	        								$return[] = $newid;
	        											
	        								if((isset($_POST['folder']) && $_POST['folder'] == -1) || !isset($_POST['folder'])){ 		// upload to album
		        								if($this->addImageToFolder($newid, $folder) !== false){
	
		        									//TODO: save Image Meta data	
	// 	        									if(isset($exif['FlashUsed'])) $this->updateMetaDataForImage($newid, $this->config['exif']['flash_id'], $exif['FlashUsed']);
	// 	        									if(isset($exif['model'])) $this->updateMetaDataForImage($newid, $this->config['exif']['model'], $exif['model']);
		        												
		        								//	$this->_msg(str_replace('{@pp:file}', $img['name'], $this->_('UPLOAD_SUCCESS')), Messages::INFO);
		        									$success++;
		        								} else {
		        									$this->deleteImageByPath($newfilepath);
		        									$this->_msg($this->_('DATABASE_ERROR', 'database'));
		        								}
		        								//$this->debugVar($add.'-1');
	        								} 
	        							} else {
	        								unlink($GLOBALS['to_root'].$newfilepath);
	        								$this->_msg($this->_('DATABASE_ERROR', 'database'), Messages::ERROR);
	        								array_pop($return);
	        							}
	        						} else {
	        							$this->_msg($this->_('Uploaded file does not exist', 'database'), Messages::ERROR);
	        						}
	        					} else $this->_msg($this->_('ERROR_UPLOAD_DIR_NOT_EXISTS'), Messages::RUNTIME_ERROR);
	        				} else $this->_msg(str_replace('{@pp:file}', $img['name'], $this->_('ERROR_WRONG_FORMAT')), Messages::RUNTIME_ERROR);
	        			} else $this->_msg(str_replace('{@pp:file}', $img['name'], $this->_('ERROR_MAX_FILE_SIZE')), Messages::RUNTIME_ERROR);
					}
					$this->_msg(str_replace('{@pp:count}', $success, $this->_('{@pp:count} files uploaded successfully')), Messages::INFO);
					return $return;
				} else {
					$this->_msg($this->_('_No Files selected', 'gallery'), Messages::ERROR);
					return null;
				}
			} else {
				$this->_msg($this->_('_Could not upload', 'gallery'), Messages::ERROR);
				return null;
			}
		}

		/**
		 * Replaces Image with uploaded Image
		 * @param unknown_type $image
		 * @param unknown_type $up_img_array | uploaded Image Array - if there are more than one images the first one will be used 
		 */
		public function replaceImage($image, $replace_image_array) {
			error_log('replace Image: '.$image);
			if(!is_object($image) || get_class($image) != 'GalleryImage') $image = $this->getImageById($image);
				
			if($image != null){
				if(is_array($replace_image_array) && $replace_image_array != array()) {
					if(isset($replace_image_array['size'])) { // images is a real image array
						if($replace_image_array['size'] <= $this->_setting('upload.max_file_size')){ 										// check size again
							if(preg_match("/\." . $this->_setting('upload.valid_file_types') . "$/i", $replace_image_array['name'])){	// check types
								if(is_dir($GLOBALS['to_root'].$this->_setting('upload.upload_dir'))){									// check if upload dir exists
			
									$exts = explode('.', strtolower($replace_image_array['name']));
			
									$newfilepath = $this->_setting('upload.upload_dir').$this->_setting('upload.upload_prefix').str_replace(array('.', ' '), array('', ''), microtime()).'.'.$exts[count($exts)-1];
			
									if(copy($replace_image_array['tmp_name'], $GLOBALS['to_root'].$newfilepath)){ // moving to final destinition (copy instead of move_uploaded_file)
										unlink($replace_image_array['tmp_name']);
			
										//exifdata
										$exif = new phpExifReader($GLOBALS['to_root'].$newfilepath);

										$exif = $exif->getImageInfo();
											
										$shot_date = (isset($exif['dateTimeDigitized'])) ?  $exif['dateTimeDigitized'] :'';
										
										// new part - different from uploadImage
										error_log($newfilepath);
										// replace new Image in Database
										$query = $this->mysqlBool('UPDATE `'.$GLOBALS['db']['db_prefix'].'gallery_image` SET  
																		name="'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($replace_image_array['name'])).'",
																		path="'.$newfilepath.'",
																		hash="'.md5_file($GLOBALS['config']['root'].$newfilepath).'",
																		s_date="'.$shot_date.'",
																		c_date="'.microtime().'",
																		size="'.filesize($GLOBALS['config']['root'].$newfilepath).'"
																	WHERE i_id="'.$image->getId().'";');
										
										if($query){
											// remove old files
											unlink($GLOBALS['to_root'].$image->getPath());
											
											// clear cache
											$this->sp->ref('Image')->clearCache($image->getPath());
											return true;
										}
									} else {
										$this->_msg($this->_('Uploaded file does not exist', 'database'), Messages::ERROR);
									}
								} else $this->_msg($this->_('ERROR_UPLOAD_DIR_NOT_EXISTS'), Messages::RUNTIME_ERROR);
							} else $this->_msg(str_replace('{@pp:file}', $replace_image_array['name'], $this->_('ERROR_WRONG_FORMAT')), Messages::RUNTIME_ERROR);
						} else $this->_msg(str_replace('{@pp:file}', $replace_image_array['name'], $this->_('ERROR_MAX_FILE_SIZE')), Messages::RUNTIME_ERROR);
					}
// 					$this->_msg(str_replace('{@pp:count}', $success, $this->_('{@pp:count} files uploaded successfully')), Messages::INFO);
					return false;
				} else {
					$this->_msg($this->_('_No Files selected', 'gallery'), Messages::ERROR);
					return null;
				}
			} else {
				$this->_msg($this->_('_Could not upload', 'gallery'), Messages::ERROR);
				return null;
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