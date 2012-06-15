<?php
	class UIW_Text extends AUIWidget {
	
		private $value;
		private $label;
		
		private $id;
		private $name;
		private $type;
	
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
        
        protected function setType($type){
        	$this->type = $type;
        }
        
		public function render() {
			$vd = new ViewDescriptor(AUIWidget::TPL_ROOT.'text');
			$vd->addValue('value', $this->value);
			
			if($this->label != ''){
				$lvd = new SubViewDescriptor('label');
				$lvd->addValue('label', $this->label);
				$vd->addSubView($lvd);
			} else {
				$vd->removeSubView('label');
			}
			
			return $vd->render();
		}
	
	}
?>