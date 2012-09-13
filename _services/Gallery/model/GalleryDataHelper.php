<?php
	
	class GalleryDataHelper extends TFCoreFunctions{
		protected $name = 'Gallery';
		
		const STATUS_HIDDEN = 0;
		const STATUS_ONLINE = 1;
		const STATUS_OFFLINE = 2;
		
		function __construct($settings){
			parent::__construct();
			$this->settings = $settings;
		}	
		
		/* Folders */
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
		
		/* images */
		public function getImagesByFolderId($folderId, $page=-1, $status=array()){
			// get count of images
			$count = $this->getImageCountByFolderId($folderId, $status);
			
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
					gif.f_id = "'.mysql_real_escape_string($folderId).'" '.$user.$status.$limit);
			
			if($pp != ''){
				$return = array();
				foreach($pp as $p){
					$return[] = new GalleryImage($p['i_id'], $p['name'], $p['path'], $p['hash'], $p['status'], $p['u_id'], $p['c_date'], $p['size'], $p['s_date']);
				}
				return $return;
			} else return null;
		}
		
		public function getImageCountByFolderId($folderId, $status) {
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
														gif.f_id = "'.mysql_real_escape_string($folderId).'" '.$user.$status);
			
			if($pp != array()){
				return $pp['count'];
			} else return 0;
		}
	}
?>