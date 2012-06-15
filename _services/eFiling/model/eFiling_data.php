<?php
	class eFiling_data {
		private $id;
		private $name;
		private $info;
		private $type;
		private $public;
		private $order;
		private $send;
		private $value;
		
		function __construct($id, $group, $name, $info, $type, $public, $order, $send){
			$this->id = $id;
			$this->name = $name;
			$this->info = $info;
			$this->type = $type;
			$this->public = $public;
			$this->order = $order;
			$this->send = $send;
		}
		
		public function setValue($value){
			$this->value = $value;
		}
		
		/* getter */
		public function getId() { return $this->id; }
		public function getName() { return $this->name; }
		public function getInfo() { return $this->info; }
		public function getType() { return $this->type; }
		public function getGroup() { return $this->group; }
		public function isPublic() { return $this->public; }
		public function getOrder() { return $this->order; }
		public function getSend() {return $this->send; }
		
		public function getValue() { return $this->value; }
		
	}
?>