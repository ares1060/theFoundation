<?php
	/**
	 * Enter description here ...
	 * @author MisterE
	 *
	 */
	class GalleryFolder {
		private $id;
		private $parentFolderId;
		private $userId;
		private	$name;
		private $creationDate;
		
		private $left;
		private $right;
		
		function GalleryFolder($id, $parentFolder_id, $user_id, $name, $creationDate, $left, $right) {
			$this->id = $id;
			$this->parentFolderId = $parentFolder_id;
			$this->userId = $user_id;
			$this->name = $$name;
			$this->creationDate = $creationDate;
			
			$this->left = $left;
			$this->right = $right;
		}
		
		// Getter + Setter
		function getId () {
			return $this->id;
		}
		
		function setId ($id) {
			$this->id = $id;
		}
		
		function getParentFolderId () {
			return $this->parentFolderId;
		}
		
		function setParentFolderId ($parentFolderId) {
			$this->parentFolderId = $parentFolderId;
		}
		
		function getUserId () {
			return $this->userId;
		}
		
		function setUserId ($userId) {
			$this->userId = $userId;
		}
		
		function getRight () {
			return $this->right;
		}
		
		function setRight ($right) {
			$this->right = $right;
		}
		
		function getLeft () {
			return $this->left;
		}
		
		function setLeft ($left) {
			$this->left = $left;
		}
		
		function getCreationDate () {
			return $this->creationDate;
		}
		
		function setCreationDate ($creationDate) {
			$this->creationDate = $creationDate;
		}
		
		function getName () {
			return $this->name;
		}
		
		function setName ($name) {
			$this->name = $name;
		}
		
	}
?>