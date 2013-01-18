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
		function getFolders($page=-1, $status=-1) {
			// get count of all products
			$count = $this->getFolderCount($status);

			// create user, status and category string 
			$user = 'WHERE `u_id`="'.mysql_real_escape_string($this->sp->ref('User')->getViewingUser()->getId()).'"';
			$status = ($status == -1) ? '' : ' AND `status`="'.mysql_real_escape_string($status).'"';
			$cat = ($cat_id == -1) ? '' : ' AND `cat`="'.mysql_real_escape_string($cat_id).'"';

			// create limit string
			$per_page = $this->_setting('admin.per_page.products');
        	$limit = ($page == -1) ? '' : 'LIMIT '.(mysql_real_escape_string($page-1)*mysql_real_escape_string($per_page)).', '.mysql_real_escape_string($per_page).';';

        	$pp = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'shop_products` '.$user.$status.$cat.' '.$limit);

        	if($pp != ''){
				$return = array();
				foreach($pp as $p){
					$cat = $this->sp->ref('Category')->getCategory($p['cat']);
					$return[] = new ShopProduct($p['p_id'], $p['status'], $p['name'], $p['desc'], $p['price'], $p['weight'], $p['datum'], $p['u_id'], $cat, $p['stock'], $p['download'], $p['filesize'], $p['file_hash'], $p['dimensions'], $p['img_id'], $p['stock_nr'], $p['t_nr']);
				}
				return $return;
			} else return null;
		}
	}
?>