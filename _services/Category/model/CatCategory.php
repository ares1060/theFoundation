<?php
	class CatCategory{
		private $name;
		private $id;
		private $webname;
		private $img;
		private $isServiceRoot;
		private $status;
		private $desc;
		
		function __construct($id, $name, $webname, $status, $desc, $img=0, $serviceroot=0){
			$this->id = $id;
			$this->name = $name;
			$this->webname = $webname;
			$this->status = $status;
			$this->desc = $desc;
			$this->img = $img;
			$this->isServiceRoot = ($serviceroot == 1);
		}
		
		public function getId() { return $this->id; }
		public function getName() { return $this->name; }
		public function getWebName() { return $this->webname; }
		public function getImg() { return $this->img; }
		public function isServiceRoot() { return $this->isServiceRoot; }
		public function getStatus() { return $this->status; }
		public function getDesc() { return $this->desc; }
	}
?>