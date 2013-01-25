<?php
	class ContactFormElement {
		private $id;
		private $type;
		private $name;
		private $label;
		private $forced;
		private $sortid;
		private $combogroup;
		private $param;
		
		function __construct($id, $type, $name, $label, $forced, $combogroup, $sortid, $param){
			$this->id = $id;
			$this->type = $type;
			$this->name = $name;
			$this->label = $label;
			$this->forced = $forced;
			$this->combogroup = $combogroup;
			$this->sortid = $sortid;
			$this->param = $this->parseParams($param);
		}
		
		private function parseParams($param){
			$r = array();
			foreach(explode(';', $param) as $p){
				$tmp = explode(':', $p);
				if(count($tmp) == 2) $r[$tmp[0]] = $tmp[1];
			}
			return $r;
		}
		
		public function getparam($name){
			if(isset($this->param[$name])) return $this->param[$name];
			else return null;
		}
		
		/**
		 * @return the $id
		 */
		public function getId() {
			return $this->id;
		}
	
			/**
		 * @return the $type
		 */
		public function getType() {
			return $this->type;
		}
	
			/**
		 * @return the $name
		 */
		public function getName() {
			return $this->name;
		}
	
			/**
		 * @return the $label
		 */
		public function getLabel() {
			return $this->label;
		}
	
			/**
		 * @param field_type $id
		 */
		public function setId($id) {
			$this->id = $id;
		}
	
			/**
		 * @param field_type $type
		 */
		public function setType($type) {
			$this->type = $type;
		}
	
			/**
		 * @param field_type $name
		 */
		public function setName($name) {
			$this->name = $name;
		}
	
			/**
		 * @param field_type $label
		 */
		public function setLabel($label) {
			$this->label = $label;
		}
		
		public function setForced($forced){
			$this->forced = $forced;
		}
		
		public function isForced() {
			return $this->forced;
		}
	}
?>