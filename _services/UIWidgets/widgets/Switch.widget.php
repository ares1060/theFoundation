<?php
	class UIW_Switch extends AUIWidget {
	
		private $value;
		private $label;
		
		private $id;
		private $name;
		private $type;
		
		const TYPE_ON_OFF = 0;
		const TYPE_LABEL = 1;
	
		function __construct(){
			$this->label = '';
			$this->type = self::TYPE_ON_OFF;
		}
        
		public function setId($id){
        	$this->id = $id;
        }
        
		public function setName($name){
        	$this->name = explode('|', $name);
        	if(count($this->name) > 1) $this->type = self::TYPE_LABEL;
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
			$vd = new ViewDescriptor(AUIWidget::TPL_ROOT.'switch');
			
			if($this->type == self::TYPE_ON_OFF){
				$vd->addValue('name_0', 'On');
				$vd->addValue('name_1', 'Off');
			} else {
				$vd->addValue('name_0', $this->name[0]);
				$vd->addValue('name_1', $this->name[1]);
			}
			
			
			
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