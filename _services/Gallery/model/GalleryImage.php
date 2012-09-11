<?php
/**
 * Gallery Image model class
 * @author matthias
 *
 */
class GalleryImage {
	private $id;
	private $name;
	private $path;
	private $hash;
	private $status;
	private $uId;
	private $creationDate;
	private $shotDate;
	private $size;
	
	private $folders;
	
	function GalleryImage($id, $name, $path, $hash, $status, $uId, $creationDate, $size, $shotDate) {
		$this->id = $id;
		$this->name = $name;
		$this->path = $path;
		$this->hash = $hash;
		$this->uId = $uId;
		$this->creationDate = $creationDate;
		$this->shotDate = $shotDate;
		$this->size = $size;
	}
	
	function addToFolder($folderId) {
		$this->folders [] = $folderId;
	}
	
	// Getter + Setter
	
	/**
	 *
	 * @return the $shotDate
	 */
	public function getShotDate() {
		return $this->shotDate;
	}
	
	/**
	 *
	 * @param $shotDate field_type       	
	 */
	public function setShotDate($shotDate) {
		$this->shotDate = $shotDate;
	}
	/**
	 *
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
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
	 * @return the $path
	 */
	public function getPath() {
		return $this->path;
	}
	
	/**
	 *
	 * @return the $hash
	 */
	public function getHash() {
		return $this->hash;
	}
	
	/**
	 *
	 * @return the $status
	 */
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 *
	 * @return the $uId
	 */
	public function getUId() {
		return $this->uId;
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
	 * @return the $size
	 */
	public function getSize() {
		return $this->size;
	}
	
	/**
	 *
	 * @return the $folders
	 */
	public function getFolders() {
		return $this->folders;
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
	 * @param $name field_type       	
	 */
	public function setName($name) {
		$this->name = $name;
	}
	
	/**
	 *
	 * @param $path field_type       	
	 */
	public function setPath($path) {
		$this->path = $path;
	}
	
	/**
	 *
	 * @param $hash field_type       	
	 */
	public function setHash($hash) {
		$this->hash = $hash;
	}
	
	/**
	 *
	 * @param $status field_type       	
	 */
	public function setStatus($status) {
		$this->status = $status;
	}
	
	/**
	 *
	 * @param $uId field_type       	
	 */
	public function setUId($uId) {
		$this->uId = $uId;
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
	 * @param $size field_type       	
	 */
	public function setSize($size) {
		$this->size = $size;
	}
	
	/**
	 *
	 * @param $folders field_type       	
	 */
	public function setFolders($folders) {
		$this->folders = $folders;
	}

}
?>