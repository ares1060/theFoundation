<?php
	class GalleryMeta {
		private $id;
		private $name;
		private $type;
		private $group;
		private $desc;
		private $readOnly;
		
		private $iId;
		private $value;
		private $groupName;
		private $label;
		
		function __construct($id, $name, $type, $group, $desc, $iId=-1, $value='', $readOnly=false) {
			$this->id = $id;
			$this->name = $name;
			$this->type = $type;
			$this->desc = $desc;
			$this->group = $group;
			$this->iId = $iId;
			$this->value = $value;
			$this->label = '';
			$this->groupName = '';
			$this->readOnly = $readOnly;
		}
		
		/* --- setters ---- */
		public function setValue($value){
			$this->value = $value;
		}
		public function setImageId($iId){
			$this->iId = $iId;
		}
		public function setGroupName($name){
			$this->groupName = $name;
		}
		public function setLabel($label) {
			$this->label = $label;
		}
		public function setType($type){
			$this->type = $type;
		}
		/* --- getters --- */
		public function getId() {return $this->id;}
		public function getName() {return $this->name;}
		public function getType() {return $this->type;}
		public function getValue() {return $this->value;}
		public function getGroup() {return $this->group;}
		public function getDescription() {return $this->desc;}
		public function getImageId() {return $this->iId;}
		public function getGroupName() {return $this->groupName;}
		public function getLabel() {return $this->label;}
		public function isReadOnly() {return $this->readOnly;}
	}
?>