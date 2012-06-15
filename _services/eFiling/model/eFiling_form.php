<?php
	class eFiling_form {
		private $id;
		private $name;
		private $desc;
		private $content;
		private $from;
		private $to;
		private $preview;
		
		function __construct($id, $name, $desc, $from, $to, $preview){
			$this->id = $id;
			$this->name = $name;
			$this->desc = $desc;
			$this->content = array();
			$this->preview = ($preview=='') ? array() : explode(',', $preview);
			
			if((int)$from == $from) $this->from = $from;
			else $this->from = strtotime($from);

			if((int)$to == $to) $this->to = $to;
			else $this->to = strtotime($to);
		}
		
		public function addContent($datagroup){
			if(is_array($datagroup) && isset($datagroup[0]) && get_class($dataroup[0]) == 'eFiling_datagroup') array_merge($this->content, $datagroup); 
			else if(get_class($datagroup) == 'eFiling_datagroup') $this->content[] = $datagroup;
		}
		
		public function setContent($datagroup) {
			if(is_array($datagroup) && isset($datagroup[0]) && get_class($datagroup[0]) == 'eFiling_datagroup') $this->content = $datagroup;
		}
		
		/* getter */
		public function getId() { return $this->id; }
		public function getName() { return $this->name; }
		public function getDesc() { return $this->desc; }
		public function getContentCount() { return count($this->content); }
		
		public function getContent() { return $this->content; }
		
		public function getFrom() { return $this->from; }
		public function getTo() { return $this->to; }
		public function getPreview() { return $this->preview; }

		public function isActive() {
			$now = time(); 
			return ($now > $this->from && $now < $this->to);
		}
	}
?>