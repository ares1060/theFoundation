<?php
	class CatTreeNode{
		private $id;
		private $category;
		private $parent;
		private $children;
		private $root;
		private $left;
		private $right;
		private $parent_id;
		
		function __construct($id, $category, $left=-1, $right=-1, $parent_id=-1){
			$this->id = $id;
			$this->root = true;
			$this->children = array();
			$this->category = $category;
			$this->left = $left;
			$this->right = $right;
			$this->parent_id = $parent_id;
			$this->parent = null;
		}
		
		public function getId() { return $this->id; }
		public function getCategory() { return $this->category; }
		public function getParent() { return $this->parent; }
		public function getChildren() { return $this->children; }
		public function getLeft() { return $this->left; }
		public function getRight() { return $this->right; }
		public function getParentId() { return $this->parent_id; }
		
		public function setLeft($left) { $this->left = $left; }
		public function setRight($right) { $this->right = $right; }
		
		public function addChildren(&$children) {
			if(get_class($children) == 'CatTreeNode'){
				$this->children[] = $children;
				return $children->setParent($this);
			}
		}
		
		public function setParent(CatTreeNode &$parent){
			if($parent->getId() != null){
				$this->parent_id = $parent->getId();
				$this->parent = $parent;
				$this->root = false;
				return $this;
			} else return null;
		}
	}
?>