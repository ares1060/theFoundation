<?php
	class eFiling_filing {
		private $id;
		private $form;
		private $datum;
		private $status;
		private $preview;
		private $backup;
		
		function __construct($id, $form, $datum, $status, $preview, $backup){
			$this->id = $id;
			$this->form = $form;
			$this->datum = $datum;
			$this->status = $status;
			$this->preview = $preview;
			$this->backup = $backup;
		}
		
		function getId() { return $this->id; }
		function getForm() { return $this->form; }
		function getDatum() { return $this->datum; }
		function getStatus() { return $this->status.''; }
		function getPreview() { return $this->preview; }
		function getBackup() { return $this->backup; }
		
		
	}