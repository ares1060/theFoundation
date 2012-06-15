<?php
	class GalleryImage {
		private $id;
		private $name;
		private $path;
		private $hash;
		private $status;
		private $date;
		private $userId;
		private $meta;
		private $shotDate;
		
		function __construct($id, $name, $path, $hash, $status, $date, $userId, $shot_date=null){
			$this->id = $id;
			$this->name = $name;
			$this->path = $path;
			$this->hash = $hash;
			$this->status = $status;
			$this->date = $date;
			$this->userId = $userId;
			$this->shotDate = $shot_date;
			$this->meta = array();
		}
		/* -- setters -- */
		public function addMeta($meta){
			$this->meta[] = $meta;
		}
		
		public function addMetas($metas){
			foreach($metas as $meta) $this->addMeta($meta);
		}
		
		/* -- getters -- */
		public function getId() {return $this->id;}
		public function getName() {return $this->name;}
		public function getPath() {return $this->path;}
		public function getHash() {return $this->hash;}
		public function getStatus() {return $this->status;}
		public function getUploadDate() {return $this->date;}
		public function getUserId() {return $this->userId;}
		public function getMeta() {return $this->meta;}
		public function getShotDate() {return $this->shotDate;}
	}
?>