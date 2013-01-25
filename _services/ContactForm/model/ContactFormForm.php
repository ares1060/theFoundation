<?php
	class ContactFormForm {
		private $elements;
		private $mail;
 		private $id;
 		private $name;
 		private $u_id;
 		private $created;
 		
 		private $dataHelper;
		
// 		function __construct($id, $name, $elements=array()){
		function __construct(ContactFormDataHelper $dataHelper, $id, $name, $mail, $u_id, $created, $elements=array()){
			$this->elements = isset($elements) ? $elements : $this->loadElements($dataHelper);
 			$this->id = $id;
  			$this->name = $name;
  			$this->mail = $mail;
  			$this->dataHelper = $dataHelper;
  			$this->u_id = $u_id;
  			$this->created = $created;
		}
		
		public function addElement(ContactFormElement $element) {
			$this->elements[] = $element;
		}
		
		public function loadElements(ContactFormDataHelper $dataHelper){
			$this->elements = $dataHelper->getElements();
		}
		
		/**
		 * @return the $elements
		 */
		public function getElements() {
			return $this->elements;
		}
	
			/**
		 * @return the $id
		 */
		public function getId() {
			return $this->id;
		}
	
			/**
		 * @return the $name
		 */
		public function getName() {
			return $this->name;
		}
	
			/**
		 * @param field_type $elements
		 */
		public function setElements($elements) {
			$this->elements = $elements;
		}
	
			/**
		 * @param field_type $id
		 */
		public function setId($id) {
			$this->id = $id;
		}
	
			/**
		 * @param field_type $name
		 */
		public function setName($name) {
			$this->name = $name;
		}
		/**
		 * @return the $mail
		 */
		public function getMail() {
			return $this->mail;
		}
	
			/**
		 * @return the $u_id
		 */
		public function getU_id() {
			return $this->u_id;
		}
	
			/**
		 * @return the $created
		 */
		public function getCreated() {
			return $this->created;
		}
	
			/**
		 * @param field_type $mail
		 */
		public function setMail($mail) {
			$this->mail = $mail;
		}
	
			/**
		 * @param field_type $u_id
		 */
		public function setU_id($u_id) {
			$this->u_id = $u_id;
		}
	
			/**
		 * @param field_type $created
		 */
		public function setCreated($created) {
			$this->created = $created;
		}
	}
?>