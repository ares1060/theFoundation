<?php
	class UIW_TextArea extends AUIWidget {
	
		private $name;
		private $value;
		private $label;
		private $id;
		private $width;
		private $height;
		private $style;
	
		function __construct(){
			$this->label = '';
		}
        
		public function setId($id){
        	$this->id = $id;
        }
        
		public function setName($name){
        	$this->name = $name;
        }
        
		public function setValue($value){
        	$this->value = $value;
        }
        
        public function setLabel($label){
        	$this->label = $label;
        }

        public function setHeight($height){
        	$this->height = $height;
        }
        
	    public function setWidth($width){
        	$this->width = $width;
        }
        
        public function setStyle($style){
        	$this->style = $style;
        }
		
		public function render() {
			$vd = new ViewDescriptor(AUIWidget::TPL_ROOT.'textarea');
			$vd->addValue('id', $this->id);
			$vd->addValue('name', $this->name);
			$vd->addValue('value', $this->value);
			$vd->addValue('height', $this->height);
			$vd->addValue('style', $this->style);
			$vd->addValue('width', $this->width);
			if($this->label != ''){
				$lvd = new SubViewDescriptor('label');
				$lvd->addValue('label', $this->label);
				$lvd->addValue('type', 'input');
				$lvd->addValue('id', $this->id);
				$vd->addSubView($lvd);
			} else {
				$vd->removeSubView('label');
			}
			
			return $vd->render();
		}
	
	}
?>