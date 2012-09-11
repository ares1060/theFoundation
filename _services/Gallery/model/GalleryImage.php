<?php
	class GalleryImage {
		private $id;
		private $name;
		private $path;
		private $hash;
		private $status;
		private $uId;
		private $creationDate;
		private $size;
		
		private $folders;

		function GalleryImage($id, $name, $path, $hash, $status, $uId, $creationDate, $size){
			$this->id = $id;
			$this->name = $name;
			$this->path = $path;
			$this->hash = $hash;
			$this->uId = $uId;
			$this->creationDate = $creationDate;
			$this->size = $size;
		}
		
		function addToFolder($folderId){
			$this->folders[] = $folderId;
		}
		
		//Getter + Setter
		function getFolders () {
			return $this->folders;
		}
		
		function setFolders ($folders) {
			$this->folders = $folders;
		}
		
		function getSize () {
			return $this->size;
		}
		
		function setSize ($size) {
			$this->size = $size;
		}
		
		function getCreationDate () {
			return $this->creationDate;
		}
		
		function setCreationDate ($creationDate) {
			$this->creationDate = $creationDate;
		}
		
		function getUId () {
			return $this->uId;
		}
		
		function setUId ($uId) {
			$this->uId = $uId;
		}
		
		function getStatus () {
			return $this->status;
		}
		
		function setStatus ($status) {
			$this->status = $status;
		}
		
		function getHash () {
			return $this->hash;
		}
		
		function setHash ($hash) {
			$this->hash = $hash;
		}
		
		function getPath () {
			return $this->path;
		}
		
		function setPath ($path) {
			$this->path = $path;
		}
		
		function getName () {
			return $this->name;
		}
		
		function setName ($name) {
			$this->name = $name;
		}
		
		function getId () {
			return $this->id;
		}
		
		function setId ($id) {
			$this->id = $id;
		}
	}
?>