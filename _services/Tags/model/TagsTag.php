<?php
	class TagsTag{
		private $id;
		private $name;
		private $webname;
		
		function __construct($id, $name, $webname){
			$this->id = $id;
			$this->name = $name;
			$this->webname = $webname;
		}	
		
		/* getters */
		public function getId() { return $this->id; }
		public function getName() { return $this->name; }
		public function getWebname() { return $this->webname; }
		
	}
?>