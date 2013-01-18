<?php
	class ShopDataHelper extends TFCoreFunctions{
		protected $name = 'Shop';
		
		const STATUS_HIDDEN = 0;
		const STATUS_ONLINE = 1;
		
		function __construct($settings){
			parent::__construct();
			$this->settings = $settings;
		}	
		
		/** ---  Getter --- */
		/**
		 * loads Product Object from Database and returnes it 
		 * @param unknown_type $id
		 */
		public function getProduct($id) {
			$p = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'shop_products` WHERE p_id="'.mysql_real_escape_string($id).'"');
			
			if($p != ''){
				$cat = $this->sp->ref('Category')->getCategory($p['cat']);
				return new ShopProduct($p['p_id'], $p['status'], $p['name'], $p['desc'], $p['price'], $p['weight'], $p['datum'], $p['u_id'], $cat, $p['stock'], $p['download'], $p['filesize'], $p['file_hash'], $p['dimensions'], $p['img_id'], $p['stock_nr'], $p['t_nr']);
			} else return null;
		}
		
		/**
		 * loads Product Objects from Database and returnes them 
		 * @param unknown_type $id
		 */
		public function getProducts($page=-1, $status=-1, $cat_id=-1) {
			// get count of all products
			$count = $this->getProductCount($status);

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
		
		/**
		 * returnes count of all Products with status = $status
		 * @param unknown_type $status
		 */
		public function getProductCount($status=-1){
			$user = 'WHERE `u_id`="'.mysql_real_escape_string($this->sp->ref('User')->getViewingUser()->getId()).'"';
			$status = ($status == -1) ? '' : ' AND `status`="'.mysql_real_escape_string($status).'"';
			
			$p = $this->mysqlRow('SELECT COUNT(*) myCount FROM `'.$GLOBALS['db']['db_prefix'].'shop_products` '.$user.$status);
			if($p != ''){
				return $p['myCount'];
			} else return 0;
		}
		
		/** ---  SETTER --- */
		/**
		 * saves category for given product id
		 * @param $id
		 * @param $cat
		 */
		public function setProductCategory($id, $cat){
			
			if(($this->checkRight('administer_product', $id))){
				return $this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'shop_products` SET `cat`="'.mysql_real_escape_string($cat).'" WHERE `p_id`="'.mysql_real_escape_string($id).'"');
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
			}
		}
		public function setProductImage($id, $image_id){
			if(($this->checkRight('administer_product', $id))){
				return $this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'shop_products` SET `img_id`="'.mysql_real_escape_string($image_id).'" WHERE `p_id`="'.mysql_real_escape_string($id).'"');
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
			}
		}
		
		/**
		 * updates Product info in database
		 * @param unknown_type $id
		 * @param unknown_type $status
		 * @param unknown_type $name
		 * @param unknown_type $desc
		 * @param unknown_type $price
		 * @param unknown_type $weight
		 * @param unknown_type $stock
		 * @param unknown_type $download
		 * @param unknown_type $dimensions
		 */
		public function updateProduct($id, $status, $name, $desc, $price, $weight, $stock, $download, $dimensions, $stock_nr){
			if($this->checkRight('administer_product', $id)){
				// TODO: check dimensions pregmatch
				return $this->mysqlUpdate('UPDATE `'.$GLOBALS['db']['db_prefix'].'shop_products` SET
											`status`="'.mysql_real_escape_string($status).'",
											`name`="'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($name)).'",
											`desc`="'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($desc)).'",
											`price`="'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($price)).'",
											`weight`="'.mysql_real_escape_string($weight).'",
											`stock`="'.mysql_real_escape_string($stock).'",
											`download`="'.(mysql_real_escape_string(($download) ? '1' : '0')).'",
											`dimensions`="'.mysql_real_escape_string($dimensions).'",
											`stock_nr` ="'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($stock_nr)).'"
										WHERE `p_id`="'.mysql_real_escape_string($id).'"
				');
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
			}
		}
		
	 	/* new Product info in database
		 * @param unknown_type $id
		 * @param unknown_type $status
		 * @param unknown_type $name
		 * @param unknown_type $desc
		 * @param unknown_type $price
		 * @param unknown_type $weight
		 * @param unknown_type $stock
		 * @param unknown_type $download
		 * @param unknown_type $dimensions
		 */
		public function newProduct($status, $name, $desc, $price, $weight, $stock, $download, $dimensions){
			if($this->checkRight('add_product')){
				// TODO: check dimensions pregmatch

				$n_id = $this->mysqlInsert('INSERT `'.$GLOBALS['db']['db_prefix'].'shop_products` SET
											`status`="'.mysql_real_escape_string($status).'",
											`name`="'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($name)).'",
											`desc`="'.$this->sp->ref('TextFunctions')->renderUmlaute(mysql_real_escape_string($desc)).'",
											`price`="'.mysql_real_escape_string($price).'",
											`u_id`="'.mysql_real_escape_string($this->sp->ref('User')->getViewingUser()->getId()).'",
											`weight`="'.mysql_real_escape_string($weight).'",
											`stock`="'.mysql_real_escape_string($stock).'",
											`download`="'.(mysql_real_escape_string(($download) ? '1' : '0')).'",
											`dimensions`="'.mysql_real_escape_string($dimensions).'"
				');
				
				if($n_id !== false){
					// create New Folder at Shop Album
					$this->sp->ref('Gallery')->newFolder('product_'.$n_id, $this->_setting('gallery_album_id'), '', Gallery::STATUS_ONLINE);
					
					// authorize User to edit Product
					$this->sp->ref('Rights')->authorizeUser('Shop', $this->sp->ref('User')->getViewingUser()->getId(), 'administer_product', $n_id);
					
					return $n_id;
				} else return false;
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
			}
		}
		
		/**
		 * deleted Product
		 * deletes Tags, GalleryFolder and resets User Rights
		 * @param unknown_type $id
		 */
		public function deleteProduct($id) {
			if($this->checkRight('administer_product', $id)){
				$this->sp->ref('Gallery')->deleteFolder($this->sp->ref('Gallery')->getFolderByNameAndAlbum('product_'.$id, $this->_setting('gallery_album_id'))->getId());
								
				$this->sp->ref('Tags')->deleteServiceTags('Shop', $id);
				
				$this->sp->ref('Rights')->clearUserAuthorization('Shop', 'administer_product', $_SESSION['User']['id'], $id);
				
				if($this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'shop_products` WHERE `p_id`="'.mysql_real_escape_string($id).'"') !== false){
					$this->_msg($this->_('product delete success'), Messages::INFO);
					return true;
				} else {
					$this->_msg($this->_('product delete error'), Messages::ERROR);
					return false;
				}
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
			}
		}
		
		/**
		 * handles Image upload to product
		 * @param unknown_type $product_id
		 */
		public function uploadImages($product_id){
			if($this->checkRight('administer_product', $product_id)){
				$iId = $this->sp->ref('Gallery')->executeUploads(true);
				
				return ($iId != array());
				
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
			}
		}
	}
?>