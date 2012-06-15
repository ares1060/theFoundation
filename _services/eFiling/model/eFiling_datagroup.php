<?php
	class eFiling_datagroup {
		private $id;
		private $name;
		private $desc;
		private $content;
		private $order;
		
		function __construct($id, $name, $desc){
			$this->id = $id;
			$this->name = $name;
			$this->desc = $desc;
			$this->content = array();
		}
		
		public function addContent($data){
			if(is_array($data) && isset($data[0]) && get_class($data[0]) == 'eFiling_data') array_merge($this->content, $data); 
			else if(get_class($data) == 'eFiling_data') $this->content[] = $data;
		}
		
		public function setContent($data) {
			if(is_array($data) && isset($data[0]) && get_class($data[0]) == 'eFiling_data') $this->content = $data;
		}
		
		public function setOrder($order){
			$this->order = $order;
		}
		
		/* getter */
		public function getId() { return $this->id; }
		public function getName() { return $this->name; }
		public function getDesc() { return $this->desc; }
		public function getContentCount() { return count($this->content); }
		public function getOrder() { return $this->order; }
		
		public function getContent() {
			return $this->content; 
		}
		
	}
?>