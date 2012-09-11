<?php
	class GalleryFolder{
		private $id;
		private $album;
		private $name;
		private $status;
		private $u_id;
		private $date;
		private $desc;
		private $thumb;
		private $sort;
		private $sortDA;
		
		private $imageCount;
			
		function __construct($id, $album, $name, $desc, $status, $thumb=-1, $u_id=-1, $imageCount=-1, $sort=-1, $sortDA=-1){
			$this->id = $id;
			$this->album = $album;
			$this->u_id = $u_id;
			$this->name = $name;
			$this->desc = $desc;
			$this->status = $status;
			$this->thumb = $thumb;
			$this->imageCount = $imageCount;
			$this->sort = $sort;
			$this->sortDA = $sortDA;
		}
		/* -- setters -- */
		public function setImageCount($count) {
			$this->imageCount = $count;
		}
		
		public function setDate($date) {
			$this->date = $date;
		}
		
		/* -- getters -- */
		public function getId() { return $this->id;}
		public function getAlbum() { return $this->album; }
		public function getUId() { return $this->u_id;}
		public function getName() { return $this->name;}
		public function getDate() { return $this->date;}
		public function getDesc() { return $this->desc;}
		public function getStatus() { return $this->status;}
		public function getThumb() { return $this->thumb;}
		public function getImageCount() { return $this->imageCount;}
		public function getSort() { return $this->sort; }
		public function getSortDA() { return $this->sortDA; }
	}
?>