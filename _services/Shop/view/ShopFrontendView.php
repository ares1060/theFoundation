<?php
	class ShopFrontendView extends TFCoreFunctions {
		protected $name;
		
		private $dataHelper;

		function __construct($settings, $datahelper){
			parent::__construct();
			$this->setSettingsCore($settings);
			$this->name = 'Shop';
			$this->dataHelper = $datahelper;
			
		}	
		/** ===========  view product  =========== **/
		/**
		 * returnes rendered template from given product
		 * @param ShopProduct $product
		 */
		private function tplViewProduct(ShopProduct $product){
			$tpl = new ViewDescriptor($this->_setting('tpl.view/product', 'tpl'));

			$tpl->addValue('id', $product->getId());
			$tpl->addValue('cat', $product->getCategory()->getWebname());
			$tpl->addValue('name', $product->getName());
			$tpl->addValue('price', $product->getPrice());
			$tpl->addValue('shop_album', $this->_setting('gallery_album_id', 'tpl'));
			$tpl->addValue('desc', $this->sp->ref('TextFunctions')->renderBBCode($product->getDesc()));
			$image = $this->sp->ref('Gallery')->getImage($product->getImageId());
			$tpl->addValue('img_path', ($image == null) ? $this->sp->ref('Gallery')->getNoImagePath() : $image->getPath());
			
			$tpl->addValue('tags', $this->sp->ref('Tags')->tplGetTags($this->name, $product->getid(), '../tag/{webname}/'));
			
			return $tpl->render();
		}
		
		/**
		 * Link is id_name
		 * if name is wrong you will be redirected to right page
		 * @param unknown_type $id_link
		 */
		public function tplViewProductByLink($id_link){
			$ar = explode('_', $id_link);
			$id = $ar[0];
        	unset($ar[0]);
        	$name = implode('_', $ar);
        	
        	$product = $this->dataHelper->getProduct($id);
        	
        	if($product != null){
        		if($name != $this->sp->ref('TextFunctions')->string2Web($product->getName())) header('Location: '.$_SERVER["REQUEST_URI"].'../'.$id.'_'.$this->sp->ref('TextFunctions')->string2Web($product->getName()).'/');
				else return $this->tplViewProduct($product);
        	} else {
        		$this->_msg($this->_('_product not found'), Messages::ERROR);
				return '';
        	}
		}
		
		/** ===========  view category  =========== **/
		
		/**
		 * returnes rendered view of specified Category
		 * 
		 * @param $cat_id
		 */
		private function tplViewCategory($cat, $page){
			if($cat != null && get_class($cat) == 'CatTreeNode'){
				
				$children = $this->sp->ref('Category')->getChildrenForCategory($cat->getId(), Category::STATUS_ONLINE);
				$tpl = new ViewDescriptor($this->_setting('tpl.view/categories', 'tpl'));
				
				$path = $this->sp->ref('Category')->getCategoryPath($cat->getId());
				
				// used to link to shop root 
				// if link structure is: shop/cat/x/ link will be ../../cat/y/ 
				$pre_link = ($cat->getCategory()->isServiceRoot()) ? '' : '../../';
				
				$tpl->addValue('pre_link', $pre_link);
				$tpl->addValue('id', $cat->getCategory()->getId());
				$tpl->addValue('name', $cat->getCategory()->getName());
				$tpl->addValue('webname', $cat->getCategory()->getWebname());
				
				foreach(array_reverse($path) as $p){
					$s = new SubViewDescriptor('breadcrumb');
					$s->addValue('name', $p->getName());
					$s->addValue('webname', $p->getWebname());
					$s->addValue('pre_link', $pre_link);
					
					$tpl->addSubView($s);
					unset($s);
				}
				
				foreach($children as $child){
					$img = $this->sp->ref('Gallery')->getImage($child->getCategory()->getImg());

					$s = new SubViewDescriptor('child');
					$s->addValue('name', $child->getCategory()->getName());
					$s->addValue('id', $child->getCategory()->getId());
					$s->addValue('img_path', $img->getPath());
					$s->addValue('webname', $child->getCategory()->getWebname());
					$s->addValue('pre_link', $pre_link);
					
					$tpl->addSubView($s);
					unset($s);
				}
				
				$products = $this->dataHelper->getProducts($page, ShopDataHelper::STATUS_ONLINE, $cat->getCategory()->getId());
				
				foreach($products as $p){
					$img = $this->sp->ref('Gallery')->getImage($p->getImageId());
					
					$s = new SubViewDescriptor('product');
					$s->addValue('name', $p->getName());
					$s->addValue('webname', $this->sp->ref('TextFunctions')->string2Web($p->getName()));
					$s->addValue('id', $p->getId());
					$s->addValue('img_path',  ($img == null) ? $this->sp->ref('Gallery')->getNoImagePath() : $img->getPath());
					$s->addValue('pre_link', $pre_link);
					
					$tpl->addSubView($s);
					unset($s);
				}
				
				if($products == array() && $children == array()) $tpl->showSubView('nothing_there');
				
				return $tpl->render();
			} else {
				$this->_msg($this->_('_category not found'), Messages::ERROR);
				return '';
			}
		}
		
		/**
		 * returnes rendered view of specified Category
		 * @param unknown_type $cat_id
		 */
		public function tplViewCategoryById($cat_id, $page){
			return $this->tplViewCategory($this->sp->ref('Category')->getCategory($cat_id), $page);
		}
		
		/**
		 * returnes rendered view of specified Categoryname
		 * @param unknown_type $cat_name
		 */
		public function tplViewCategoryByWebname($cat_name, $page){
			return $this->tplViewCategory($this->sp->ref('Category')->getCategoryByName($cat_name), $page);
		}
		
		/**
		 * returnes rendered view of Main Category of given service
		 * @param unknown_type $page
		 */
		public function tplViewMainCategories($page) {
			return $this->tplViewCategory($this->sp->ref('Category')->getServiceCategories($this->name), $page);
		}
		
		/** ===========  view tags  =========== **/
		
		public function tplViewTagByWebname($name){
			$tag = $this->sp->ref('Tags')->getTagByWebname($name);
			if($tag != null){
				$params = $this->sp->ref('Tags')->getParamsForTag($tag, $this->name);
				$tpl = new ViewDescriptor($this->_setting('tpl.view/tag', 'tpl'));
				
				$tpl->addValue('id', $tag->getId());
				$tpl->addValue('name', $tag->getName());
				$tpl->addValue('webname', $tag->getWebname());
				
				foreach($params as $param){
					$product = $this->dataHelper->getProduct($param);
					if($product != null){
						$s = new SubViewDescriptor('products');
						
						$s->addValue('id', $product->getId());
						$s->addValue('name', $product->getName());
						$s->addValue('webname', $this->sp->ref('TextFunctions')->string2Web($product->getName()));
						
						$image = $this->sp->ref('Gallery')->getImage($product->getImageId());
						$s->addValue('img_path', ($image == null) ? $this->sp->ref('Gallery')->getNoImagePath() : $image->getPath());
						
						$tpl->addSubView($s);
						unset($s);
					}
					unset($product);
				}
				
				return $tpl->render();
			} else {
				$this->_msg($this->_('_tag not found'), Messages::ERROR);
				return false;
			}
		}
	}
?>