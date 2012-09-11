<?php
	class GalleryDataHandler extends TFCoreFunctions{
		protected $name;
		
		private $config;

		function __construct($config){
			parent::__construct();
			$this->config = $config;
			$this->name = 'Gallery';
		}	
	
		/** ==========================================  Counts ==========================================**/
		
		/**
		 * returnes Count of Content for album or folder
		 * @param unknown_type $album_id
		 * @param unknown_type $folder_id
		 * @param unknown_type $justImages
		 */
		public function getContentCount($album_id=-1, $folder_id=-1, $status=-1, $justImages=false){
			if($album_id != -1) {
				
				$imgs = $this->getImageCountForAlbum($album_id, $status);
				
				return ($justImages) ?  $imgs : $imgs + $this->getFolderCountForAlbum($album_id, $status);
			} else if($folder_id != -1){
				
				return $this->getImageCountForFolder($folder_id, $status);
			} else return -1;
		}
		
		/**
		 * returnes count of images in given folder
		 * @param unknown_type $folder_id
		 * @param unknown_type $status
		 */
		public function getImageCountForFolder($folder_id, $status=-1){
			$status = ($status==-1) ? '' : ' AND i.status="'.mysql_real_escape_string($status).'" ';
			
        	$a = $this->mysqlRow('SELECT COUNT(*) count FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder_images fi LEFT JOIN 
        									'.$GLOBALS['db']['db_prefix'].'gallery_images i ON fi.i_id = i.i_id WHERE fi.f_id = "'.mysql_real_escape_string($folder_id).'" '.$status.';');
        	if(is_array($a)) return $a['count'];
        	else return -1;
		}
		
		/**
		 * returnes Count of Images in given Album (Folders not imcluded)
		 * @param unknown_type $album_id
		 * @param unknown_type $status
		 */
		public function getImageCountForAlbum($album_id, $status=-1){
			$status = ($status==-1) ? '' : ' AND i.status="'.mysql_real_escape_string($status).'" ';
        	
			$a = $this->mysqlRow('SELECT COUNT(*) count FROM '.$GLOBALS['db']['db_prefix'].'gallery_album_images ai LEFT JOIN 
        									'.$GLOBALS['db']['db_prefix'].'gallery_images i ON ai.i_id = i.i_id WHERE ai.a_id = "'.mysql_real_escape_string($album_id).'" '.$status.';');
        	return (is_array($a)) ? $a['count'] : -1;
		}
		
		/**
		 * returnes count of Folders in given album
		 * @param unknown_type $album_id
		 * @param unknown_type $status
		 */
		public function getFolderCountForAlbum($album_id, $status=-1){
			$status1 = ($status == -1) ? '': ' AND f.status="'.mysql_real_escape_string($status).'" ';
        	
        	$b = $this->mysqlRow('SELECT COUNT(*) count_all FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder WHERE a_id ="'.mysql_real_escape_string($album_id).'" '.$status1.';');

        	return (is_array($b)) ? $b['count_all'] : -1;
		}
		
		/** ==========================================  Album ==========================================**/
		
		
		/** ==========================================  Folder ==========================================**/
		
		/**
		 * returnes Folder By Id
		 * @param unknown_type $id
		 */
		public function getFolderById($id){
			$a = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder WHERE f_id="'.mysql_real_escape_string($id).'"');
        	
			if(is_array($a)  && $a != array()){
				return new GalleryFolder($a['f_id'], $a['a_id'], $a['name'], $a['desc'], $a['status'], $this->getImageById($a['thumb']), $a['u_id'], -1, $a['sort'], $a['sortDA']);
			} else return null;
		}
		
		/**
		 * returnes Folder By Album id and name
		 * @param unknown_type $album_id
		 * @param unknown_type $folder_name
		 */
		public function getFolderByAlbumAndName($album_id, $folder_name){
			$a = $this->mysqlArray('SELECT *  FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder
        									WHERE a_id = "'.mysql_real_escape_string($album_id).'" AND name="'.mysql_real_escape_string($folder_name).'"');
			
        	if(is_array($a) && $a != array()) {
        		return new GalleryFolder($a[0]['f_id'], $a[0]['a_id'], $a[0]['name'], $a[0]['desc'], $a[0]['desc'], $this->getImageById($a[0]['thumb']), $a[0]['u_id'], -1, $a[0]['sort'], $a[0]['sortDA']);
        	} else return null;
		}
		
		
		/** =========================================  Image ==========================================**/
		/**
		 * returnes Image by given id
		 * @param unknown_type $id
		 */
		public function getImagebyId($id) {
			$a = $this->mysqlRow('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_images WHERE i_id="'.mysql_real_escape_string($id).'"');
        	
			if(is_array($a) && $a != array()){
       			return new GalleryImage($a['i_id'], $a['name'], $a['path'], $a['hash'], $a['status'], $a['u_date'], $a['u_id'], $a['shot_date']);
        	} else return null;
		}
		
		/**
		 * returnes Images by given folder as array
		 * @param unknown_type $folder_id
		 * @param unknown_type $page
		 * @param unknown_type $perPage
		 * @param unknown_type $sort
		 * @param unknown_type $sortDA
		 * @param unknown_type $status
		 */
		public function getImagesByFolder($folder_id, $page=1, $perPage=-1, $sort=-1, $sortDA=-1, $status=-1){
			$status = ($status == -1) ? '' : ' AND status="'.mysql_real_escape_string($status).'" ';
        	$limit = ($perPage == -1) ? '' : 'LIMIT '.(mysql_real_escape_string($page-1)*mysql_real_escape_string($perPage)).', '.mysql_real_escape_string($perPage).';';
        	
        	$order = '';
        	if($sort == 'name' || $sort == Gallery::SORT_NAME){
        		$asc = ($sortDA == 'desc' || $sortDA == Gallery::SORT_DESC) ? 'DESC' : 'ASC';
        		$order = 'name '.$asc.'';
        	} else {
        		$asc = ($sortDA == 'desc' || $sortDA == Gallery::SORT_DESC) ? 'DESC' : 'ASC';
        		$order = 'i.shot_date '.$asc.', i.i_id DESC';
        	}

        	$a = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'gallery_folder_images fi LEFT JOIN 
        									'.$GLOBALS['db']['db_prefix'].'gallery_images i ON fi.i_id = i.i_id WHERE fi.f_id = "'.mysql_real_escape_string($folder_id).'" '.$status.'
        								ORDER BY '.$order.' '.$limit);
        	$return = array();
        	
        	if(is_array($a)){
	        	foreach($a as $image){
	        		$return[] = new GalleryImage($image['i_id'], $image['name'], $image['path'], $image['hash'], $image['status'], $image['u_date'], $image['u_id'], $image['shot_date']);
	        	}
        	}
        	return $return;
		}
	}
?>