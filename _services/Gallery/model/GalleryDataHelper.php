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
		function getFolders($status=-1) {

			// create user, status and category string 
			$user = 'WHERE `u_id`="'.mysql_real_escape_string($this->sp->ref('User')->getViewingUser()->getId()).'"';
			$status = ($status == -1) ? '' : ' AND `status`="'.mysql_real_escape_string($status).'"';

        	$pp = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'gallery_folder` '.$user.$status);

        	if($pp != ''){
				$return = array();
				foreach($pp as $p){
					$return[] = new GalleryFolder($p['f_id'], $p['parent'], $p['u_id'], $p['name'], $p['c_date'], $p['left'], $p['right']);
				}
				return $return;
			} else return null;
		}
		
		/**
		 * returnes folder by id
		 * @param unknown_type $id
		 * @return NULL
		 */
		function getFolderById($id) {
			if($id > -1) {
				$p = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'gallery_folder` WHERE f_id="'.mysql_real_escape_string($id).'"');
				
				if($p != '') {
					return new GalleryFolder($p['f_id'], $p['parent'], $p['u_id'], $p['name'], $p['c_date'], $p['left'], $p['right']);
				} else return null;
			} else return null;
		}
		
		/* images */
		function getImagesByFolderId($folderId, $page=-1, $status=-1){
			// get count of images
			$count = $this->getImageCountByFolderId($folderId, $status);
			
			// create limit string
			$per_page = $this->_setting('admin.per_page.images');
			$limit = ($page == -1) ? '' : ' LIMIT '.(mysql_real_escape_string($page-1)*mysql_real_escape_string($per_page)).', '.mysql_real_escape_string($per_page).';';
			
			// create user, status and category string
			$user = 'WHERE `u_id`="'.mysql_real_escape_string($this->sp->ref('User')->getViewingUser()->getId()).'"';
			$status = ($status == -1) ? '' : ' AND `status`="'.mysql_real_escape_string($status).'"';

			$pp = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'gallery_image` '.$user.$status.$limit);
			
			if($pp != ''){
				$return = array();
				foreach($pp as $p){
					$return[] = new GalleryImage($p['i_id'], $p['name'], $p['path'], $p['hash'], $p['status'], $p['u_id'], $p['c_date'], $p['size'], $p['s_date']);
				}
				return $return;
			} else return null;
		}
	}
?>