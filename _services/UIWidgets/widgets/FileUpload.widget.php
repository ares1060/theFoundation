<?php
	class UIW_FileUpload extends AUIWidget {
	
		private $name;
		private $value;
		private $label;
		private $id;
		private $max_file_size;
		private $max_uploads;
		private $type;
	
		function __construct(){
			$this->label = '';
			$this->max_uploads = 5;
			$this->max_file_size = 100000;
		}
        
		public function setId($id){
        	$this->id = $id;
        }
        
		public function setName($name){
        	$this->name = $name;
        }
	
		public function setType($type){
        	$this->type = $type;
        }
        
        public function setLabel($label){
        	$this->label = $label;
        }
        
        public function setMaxFileSize($max_file_size){
        	$this->max_file_size = $max_file_size;
        }
        
        public function setMaxUploads($size){
        	$this->max_uploads = $size;
        }
		
		public function render() {			
            $vd = new ViewDescriptor(AUIWidget::TPL_ROOT.'upload');
            
            if($this->max_uploads == 1) $vd->removeSubView('uiWidget_fileUpload_more_than_one');
            
			$vd->addValue('id', $this->id);
			$vd->addValue('name', $this->name);
			$vd->addValue('label', $this->label);
			$vd->addValue('type', $this->type);
			$vd->addValue('value', $this->value);
			$vd->addValue('max_file_size', $this->max_file_size);
			$vd->addValue('max_uploads', $this->max_uploads);
			
			return $vd->render();
		}
	
	}
?>