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
	 * @return the $left
	 */
	public function getLeft() {
		return $this->left;
	}
	
	/**
	 *
	 * @return the $right
	 */
	public function getRight() {
		return $this->right;
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
	 *
	 * @param $left field_type       	
	 */
	public function setLeft($left) {
		$this->left = $left;
	}
	
	/**
	 *
	 * @param $right field_type       	
	 */
	public function setRight($right) {
		$this->right = $right;
	}

}
?>