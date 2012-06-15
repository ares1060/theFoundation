<?php
	class ShopProduct{
		private $id;
		private $status;
		private $name;
		private $desc;
		private $price;
		private $weight;
		private $date;
		private $u_id;
		private $cat;
		private $stock;
		private $dimensions;

		private $img_id;		
		private $stock_nr;
		private $t_id;
		
		private $isDownload;
		private $filesize;
		private $hash;

		function __construct($id, $status, $name, $desc, $price, $weight, $date, $u_id, $cat, $stock, $isDownload, $filesize, $hash, $dimensions, $img_id, $stock_nr=-1, $t_id=0){
			$this->id = $id;
			$this->status = $status;
			$this->name = $name;
			$this->desc = $desc;
			$this->price = $price;
			$this->weight = $weight;
			$this->date = $date;
			$this->u_id = $u_id;
			$this->cat = $cat;
			$this->stock = $stock;
			$this->dimensions = (is_array($dimensions)) ? $dimensions : explode('x', $dimensions); 
			
			$this->isDownload = ($isDownload || $isDownload == '1');
			$this->filesize = $filesize;
			$this->hash = $hash;
			
			$this->img_id = $img_id;
			
			$this->stock_nr = $stock_nr;
			$this->t_id = $t_id;
		}
		
		/* getter */
		public function getId() { return $this->id; }
		public function getStatus() { return $this->status; }
		public function getName() { return $this->name; }
		public function getDesc() { return $this->desc; }
		public function getPrice() { return $this->price; }
		public function getWeight() { return $this->weight; }
		public function getDate() { return $this->date; }
		public function getCreatorId() { return $this->u_id; }
		public function getCategory() { return $this->cat; }
		public function getStock() { return $this->stock; }
		public function getDimensions() { return $this->dimensions; }
		
		public function isDownloadProduct() { return $this->isDownload; }
		public function getFilesize() { return $this->isDownloadProduct() ? $this->filesize : false; }
		public function getFileHash() { return $this->isDownloadProduct() ? $this->hash : false; }
		
		public function getImageId() { return $this->img_id; }
		public function getStockNr() { return $this->stock_nr; }
		public function getTaxId() { return $this->t_id; }
		
	}
?>