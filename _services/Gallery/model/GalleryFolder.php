<?php
/**
 * GalleryFolder model class
 * @author MisterE
 *
 */
class GalleryFolder {
	private $id;
	private $parentFolderId;
	private $userId;
	private $name;
	private $creationDate;
	private $status;
	
	private $root;
	
	function GalleryFolder($id, $parentFolder_id, $user_id, $name, $creationDate, $root, $status) {
		$this->id = $id;
		$this->parentFolderId = $parentFolder_id;
		$this->userId = $user_id;
		$this->name = $name;
		$this->creationDate = $creationDate;
		
		$this->root = $root;
		$this->status = $status;
	}
	
	// Getter + Setter
	
	/**
	 *
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 *
	 * @return the $parentFolderId
	 */
	public function getParentFolderId() {
		return $this->parentFolderId;
	}
	
	/**
	 *
	 * @return the $userId
	 */
	public function getUserId() {
		return $this->userId;
	}
	
	/**
	 *
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 *
	 * @return the $creationDate
	 */
	public function getCreationDate() {
		return $this->creationDate;
	}

	/**
	 *
	 * @param $id field_type       	
	 */
	public function setId($id) {
		$this->id = $id;
	}
	
	/**
	 *
	 * @param $parentFolderId field_type       	
	 */
	public function setParentFolderId($parentFolderId) {
		$this->parentFolderId = $parentFolderId;
	}
	
	/**
	 *
	 * @param $userId field_type       	
	 */
	public function setUserId($userId) {
		$this->userId = $userId;
	}
	
	/**
	 *
	 * @param $name field_type       	
	 */
	public function setName($name) {
		$this->name = $name;
	}
	
	/**
	 *
	 * @param $creationDate field_type       	
	 */
	public function setCreationDate($creationDate) {
		$this->creationDate = $creationDate;
	}
	/**
	 * @return the $root
	 */
	public function getRoot() {
		return $this->root;
	}

	/**
	 * @param field_type $root
	 */
	public function setRoot($root) {
		$this->root = $root;
	}
	/**
	 * @return the $status
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param field_type $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}
	
	
}
?>