<?php
	class ShopCart extends TFCoreFunctions implements Serializable{
		protected $name;
		
		private $dataHelper;
		
		public $contents;
		
		function __construct(){
			parent::__construct();
			$this->name = 'Shop';
			
			$this->contents = array();
		}
		
		/**
		 * serialize function 
		 * content will be serialized
		 */
		public function serialize() {
	        return serialize($this->contents);
	    }
	    /**
	     * unserialize function - just contents will be serialized
	     * @param unknown_type $data
	     */
	    public function unserialize($data) {
	        $this->contents = unserialize($data);
	    }
		
		/**
		 * setter Methods for config and dataHelper because unserialize doesn't get them right
		 */
		public function setDataHelper($dataHelper) { $this->dataHelper = $dataHelper; }
		
		/**
		 * adds product to cart
		 * @param unknown_type $product
		 */
		public function addToCart($prod, $count=1){
			$product = (get_class($prod) != 'ShopProduct') ? $this->dataHelper->getProduct($prod) : $prod;
			
			if($product != null && $count > 0){
				if($this->getCartCount() == -1) $this->initCart();
				
				if(isset($this->contents[$product->getId()])){
					$this->contents[$product->getId()]->add($count);
				} else {
					$this->contents[$product->getId()] = new ShopCartItem($product->getId(), $count);
				}
				return true;
			} else {
				$this->_msg($this->_('_could not add to cart'), Messages::ERROR);
				return false;
			}
		}
		
		/**
		 * deleted product from cart
		 * @param unknown_type $product
		 */
		public function deleteFromCart($product, $count=-1){
			$product = (get_class($prod) != 'ShopProduct') ? $this->dataHelper->getProduct($prod) : $prod;
			
			if($product != null && $count >= -1){
				if(isset($this->contents[$product->getId()])){
					if($this->contents[$product->getId()]->getCount() <= $count || $count == -1){
						unset($this->contents[$product->getId()]);
					} else {
						$this->contents[$product->getId()]->remove($count);
					} 
					return true;
				} else {
					$this->_msg($this->_('_product not in cart'), Messages::ERROR);
					return false;
				}
			} else {
				$this->_msg($this->_('_product not in cart'), Messages::ERROR);
				return false;
			}
		}
		
		/**
		 * returnes all Proucts in Cart
		 */
		public function getCartContents() {
			return $this->contents;
		}
		
		/**
		 * checks if Product is in cart
		 * @param unknown_type $prod
		 */
		public function isInCart($prod){
			$product = (get_class($prod) != 'ShopProduct') ? $this->dataHelper->getProduct($prod) : $prod;
			
			if($product != null && $count >= -1){
				return isset($this->contents[$product->getId()]);
			} else return false;
		}
		
		/**
		 * clears whole cart
		 */
		public function clearCart() {
			unset($this->contents);
			$this->initCart();
		}
		
		/**
		 * returnes count of Cart contents
		 */
		public function getCartCount() {
			$count = 0;
			if($this->contents != null){
				foreach($this->contents as $c){
					$count += $c->getCount();
				}
			} else $count = -1;
			return $count;
		}
		
		/**
		 * returnes count of prices in cart
		 */
		public function getCartPrice() {
			$price = 0;
			if($this->contents != null){
				foreach($this->contents as $c){
					$price += ($this->dataHelper->getProduct($c->getProductId())->getPrice() * $c->getCount());
				}
			} else $price = -1;
			return $price;
		}
		
		/**
		 * returnes true if cart is empty
		 */
		public function cartIsEmpty() {return $this->contents === array(); }
		
		/**
		 * inits cart
		 */
		public function initCart() {
			$this->contents = array();
		}
	}
	
	class ShopCartItem{
		private $product_id;
		private $count;
		
		function __construct($product_id, $count=1) {
			$this->product_id = $product_id;
			$this->count = $count;
		}
		
		public function getProductId() { return $this->product_id; }
		public function getCount() { return $this->count; }
		
		public function setCount($count) { $this->count = $count; }

		public function add($count) { $this->count += $count; }
		public function remove($count) { $this->count -= $count; }
	}
?>