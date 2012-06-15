<?php
	class UIW_Captcha extends AUIWidget {
	
		private $label;
		private $type;
	
		function __construct(){
			$this->label = '';
			$this->rows = 3;
		}
        
		public function setId($id){
        	$this->id = $id;
        }
        
		public function setName($name){
        	$this->name = $name;
        }
        
		public function setLabel($label){
        	$this->label = $label;
        }
	
		public function setType($type){
        	$this->type = $type;
        }
        
		public function setValue($value){
        	$this->value = $value;
        }

        public function setRows($rows){
        	$this->rows = $rows;
        }
		
		public function render() {
			$vd = new ViewDescriptor(AUIWidget::TPL_ROOT.'captcha');
			$vd->addValue('type', $this->type);
		
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