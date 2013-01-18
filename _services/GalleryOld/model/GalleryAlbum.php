<?php
	class GalleryAlbum{
		private $id;
		private $name;
		private $desc;
		private $status;
		private $thumb;
		
		private $imageCount;
			
		function __construct($id, $name, $desc, $status, $thumb, $imageCount=-1){
			$this->id = $id;
			$this->name = $name;
			$this->desc = $desc;
			$this->status = $status;
			$this->thumb = $thumb;
			$this->imageCount = $imageCount;
		}
		/* -- setters -- */
		public function setImageCount($count) {
			$this->imageCount = $count;
		}
		
		/* -- getters -- */
		public function getId() {return $this->id;}
		public function getName() {return $this->name;}
		public function getDescription() {return $this->desc;}
		public function getStatus() {return $this->status;}
		public function getThumb() {return $this->thumb;}
		public function getImageCount() {return $this->imageCount;}
	}
?>