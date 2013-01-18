<?php
	class ShopCartView extends TFCoreFunctions{
		protected $name;
		
		private $dataHelper;
		private $cart;
		
		function __construct($settings, $datahelper, $cart){
			parent::__construct();
			$this->setSettingsCore($settings);
			$this->name = 'Shop';
			$this->dataHelper = $datahelper;
			$this->cart = $cart;
		}
		
		/**
		 * 
		 */
		public function tplCart() {
			$tpl = new ViewDescriptor($this->_setting('tpl.view/cart'));
			if(!$this->cart->cartIsEmpty()){
				$t = new SubViewDescriptor('something');
				
				if($this->cart->getCartCount() > 1 ) $t->showSubView('more_than_one');
				
				$t->addValue('count', $this->cart->getCartCount());
				$t->addValue('total', $this->cart->getCartPrice());
				
				foreach($this->cart->getCartContents() as $c){
					
					$product = $this->dataHelper->getProduct($c->getProductId());
					$img = $this->sp->ref('Gallery')->getImage($product->getImageId());
					
					$a = new SubViewDescriptor('artikel');
					
					$a->addValue('anzahl', $c->getCount());
					$a->addValue('name', $product->getName());
					$a->addValue('id', $product->getStockNr());
					$a->addValue('cat_name', $product->getCategory()->getName());
					$a->addValue('cat_webname', $product->getCategory()->getWebname());
					$a->addValue('price', $product->getPrice());
					$a->addValue('img_path', ($img == null) ? '' : $img->getPath());
					$a->addValue('total', $product->getPrice()*$c->getCount());
					
					$t->addSubView($a);
					unset($a);
				}
				
				$tpl->addSubView($t);
				unset($t);
			} else $tpl->showSubView('nothing');
			return $tpl->render();	
		}
		
		/**
		 * 
		 */
		public function tplCartSmall() {
			$tpl = new ViewDescriptor($this->_setting('tpl.view/cart_small'));
			error_log($this->_setting('tpl.view/cart_small'));
			if(!$this->cart->cartIsEmpty()){
				$t = new SubViewDescriptor('something_is_here');
				
				if($this->cart->getCartCount() > 1 ) $t->showSubView('more_than_one');
				
				$t->addValue('count', $this->cart->getCartCount());
				$t->addValue('price', $this->cart->getCartPrice());
				
				$tpl->addSubView($t);
				unset($t);
			}
			return $tpl->render();	
		}
	}
?>