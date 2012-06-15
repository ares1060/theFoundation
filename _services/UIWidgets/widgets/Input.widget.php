<?php
	class Input extends AUIWidget {
	
		private $name;
		private $value;
		private $label;
		private $id;
		private $type;
		private $class;
		private $style;
	
		function __construct($type){
			$this->label = '';
			$this->type = $type;
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
        
        public function setClass($class){
        	$this->class = $class;
        }
        
        public function setType($type){
        	$this->type = $type;
        }
        
        public function setStyle($style){
        	$this->style = $style;
        }
        
        protected function preRender($vd){
        	return $vd;
        }
        
		public function render() {
			$vd = $this->preRender(new ViewDescriptor(AUIWidget::TPL_ROOT.'input'));
			$vd->addValue('id', $this->id);
			$vd->addValue('name', $this->name);
			$vd->addValue('value', $this->value);
			$vd->addValue('class', $this->class);
			$vd->addValue('style', $this->style);
			$vd->addValue('type', $this->type);
			$t = new ViewDescriptor('_services/UIWidgets/widgets/input');
			if($this->label != ''){
				$lvd = new SubViewDescriptor('label');
				$lvd->addValue('label', $this->label);
				$lvd->addValue('type', 'input');
				$lvd->addValue('id', $this->id);
				$vd->addSubView($lvd);
			}
			return $vd->render();
		}
	
	}
?>