<?php

class ShopAdminView extends TFCoreFunctions{
		protected $name;
		
		private $dataHelper;

		function __construct($settings, $datahelper){
			parent::__construct();
			$this->setSettingsCore($settings);
			$this->name = 'Shop';
			$this->dataHelper = $datahelper;
		}
		
		/** dropdowns **/
		/**
		 * returnes rendered Dropdown for statuses of Products
		 * @param $status
		 */
		private function tplDropdownProductStatus($status=-1){
			$dropdown = $this->sp->ref('UIWidgets')->getWidget('Select');

        	$dropdown->setName('pr_status');
        	$dropdown->setId('pr_status');
        	
        	$dropdown->addOption($this->_('_status_online'), ShopDataHelper::STATUS_ONLINE, $status==ShopDataHelper::STATUS_ONLINE);
        	$dropdown->addOption($this->_('_status_hidden'), ShopDataHelper::STATUS_HIDDEN, $status==ShopDataHelper::STATUS_HIDDEN);
        	
        	return $dropdown->render();
		}
		
		/**
		 * returnes rendered base Template for the Admincenter of the shop
		 */
		public function tplAdmincenter() {
			$t = new ViewDescriptor($this->_setting('tpl.admin/admincenter'));
			
			return $t->render();
		}
		
		/**
		 * returnes rendered Products Overview at given page
		 * @param unknown_type $page
		 */
		public function tplProductsOverview($page){
			if($this->checkRight('administer_product')){
				$products = $this->dataHelper->getProducts($page);
				
				$count = $this->dataHelper->getProductCount();
				
				$per_page = $this->_setting('admin.per_page.products');
	        	$number_of_pages = (ceil($count/$per_page) == 0) ? 1 : ceil($count/$per_page);
	        	$page = ($page==-1 || $page > $number_of_pages) ? 1: $page;
	        	
	        	$tpl = new ViewDescriptor($this->_setting('tpl.admin/products_overview'));
	        	
	        	$tpl->addValue('pagina_active', $page);
	        	$tpl->addValue('pagina_count', $number_of_pages);
	        		        	
	        	foreach($products as $product){
	        		$p = new SubViewDescriptor('product');
	        		
	        		$p->addValue('id', $product->getId());
	        		$p->addValue('name', $product->getName());
	        		$p->addValue('status', $product->getStatus());
	        		$p->addValue('desc', $this->sp->ref('TextFunctions')->cropText($product->getDesc(), 40));
	        		$p->addValue('price', $product->getPrice());
	        		$p->addValue('weight', $product->getWeight());
	        		$p->addValue('date', $product->getDate());
	        		$p->addValue('creator', $product->getCreatorId());
					$p->addValue('stock_nr', $product->getStockNr());
					$p->addValue('tax_id', $product->getTaxId());
	        		
	        		$image = $this->sp->ref('Gallery')->getImage($product->getImageId());
	        			        		
	        		if($image != null){
	        			$p1 = new SubViewDescriptor('thumb');
	        			$p1->addValue('path', $image->getPath());
	        			
	        			$p->addSubView($p1);
	        			unset($p1);
	        		}
	        		
	        		
	        		$cat = $product->getCategory();
	        		
	        		if($cat != null){
	        			$p->addValue('category_id', $cat->getId());
	        			$p->addValue('category_name', $cat->getName());
	        			$p->addValue('category_webname', $cat->getWebname());
	        		}
	        		$p->addValue('stock', $product->getStock());
	        		$p->addValue('stock_img', ($product->getStock()==0) ? 'empty' : (($product->getStock() >= $this->_setting('stock_full_treshold')) ? 'full' : 'normal'));
	        		$p->addValue('dimensions', implode('x', $product->getDimensions()));
	        		
	        		
	        		$p->addValue('isDownloadable', ($product->isDownloadProduct()) ? 'yes' : 'no');
	        		$p->addValue('fileSize', $product->getFilesize());
	        		$p->addValue('fileHash', $product->getFileHash());
	        		
	        		if($this->checkRight('administer_product', $product->getId())){
	        			$p1 = new SubViewDescriptor('edit');
	        			
	        			$p1->addValue('id', $product->getId());
	        			
	        			$p->addSubView($p1);
	        			unset($p1);
	        		}
	        		
	        		$tpl->addSubView($p);
	        		unset($p);
	        	}
	        	
	        	return $tpl->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
			}
		}
		
		/**
		 * returnes renderes template for a products edit page
		 */
		public function tplProductEdit($id) {
			if($this->checkRight('administer_product', $id)){
				$product = $this->dataHelper->getProduct($id);
				if($product != null){
					$t = new ViewDescriptor($this->_setting('tpl.admin/product_edit'));
			
					$t->addValue('id', $product->getId());
					$t->addValue('name', $product->getName());
					$t->addValue('status', $this->tplDropdownProductStatus($product->getStatus()));
					$t->addValue('status_id', $product->getStatus());
					$t->addValue('desc', $product->getDesc());
	        		$t->addValue('price', $product->getPrice());
	        		$t->addValue('weight', $product->getWeight());
	        		$t->addValue('date', $product->getDate());
	        		$t->addValue('creator', $product->getCreatorId());
					$t->addValue('stock_nr', $product->getStockNr());
					$t->addValue('tax_id', $product->getTaxId());
					$image = $this->sp->ref('Gallery')->getImage($product->getImageId());
					$t->addValue('img_path', $image == null ? $this->sp->ref('Gallery')->getNoImagePath() : $image->getPath());
					
	        		$cat = $product->getCategory();
	        		
	        		if($cat != null){
	        			$t->addValue('category_div', $this->sp->ref('Category')->tplCategoryTreeAdmin($this->name, $cat->getId(), $product->getId(), $this->_setting('product_category_style')));
	        			$t->addValue('category_name', $cat->getName());
	        			$t->addValue('category_id', $cat->getId());
	        			/*$p->addValue('category_webname', $cat->getWebname());*/
	        		} else {
	        			$t->addValue('category_div', $this->sp->ref('Category')->tplCategoryTreeAdmin($this->name, -1, $product->getId(), $this->_setting('product_category_style')));
	        		}
	        		
	        		$desc = $this->sp->ref('UIWidgets')->getWidget('Wysiwyg');
	        		$desc->setId('pr_desc');
	        		$desc->setName('pr_desc');
	        		//$desc->setAddImages($this->name, $product->getId());
	        		$desc->setValue($product->getDesc());
	        		
	        		$t->addValue('desc_textarea', $desc->render());
	        		
	        		$t->addValue('tags_div', $this->sp->ref('Tags')->tplGetAdminTags($this->name, $product->getId()));
	        		
	        		$t->addValue('stock', $product->getStock());
	        		$t->addValue('stock_img', ($product->getStock()==0) ? 'empty' : (($product->getStock() >= $this->_setting('product_full_threshold')) ? 'full' : 'normal'));
	        		$t->addValue('stock_threshold', $this->_setting('product_full_threshold'));
	        		
	        		$dim = $product->getDimensions();
	        		
	        		$t->addValue('width', $dim[0]);
	        		$t->addValue('height', $dim[1]);
	        		$t->addValue('depth', $dim[2]);
	        		
	        		
	        		$t->addValue('isDownloadable', ($product->isDownloadProduct()) ? 'yes' : 'no');
	        		$t->addValue('isDownloadable_checkbox', ($product->isDownloadProduct()) ? 'checked="checked"' : '');
	        		$t->addValue('fileSize', $product->getFilesize());
	        		$t->addValue('fileHash', $product->getFileHash());
	        		
					return $t->render();
				} else return '';
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
			}
		}
		/**
		 * returnes renderes template for a new Product
		 */
		public function tplProductNew() {
			if($this->checkRight('add_product')){
				$t = new ViewDescriptor($this->_setting('tpl.admin/product_new'));

	        	$t->addValue('stock_threshold', $this->_setting('product_full_threshold'));
				$t->addValue('status', $this->tplDropdownProductStatus(-1));
				
				$desc = $this->sp->ref('UIWidgets')->getWidget('Wysiwyg');
        		$desc->setId('pr_desc');
        		$desc->setName('pr_desc');
        		
        		$t->addValue('desc_textarea', $desc->render());
				
	        	$t->addValue('category_div', $this->sp->ref('Category')->tplCategoryTreeAdmin($this->name, -1, 'new', $this->_setting('product_category_style')));
				
	        	return $t->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
			}
		}
	}
?>