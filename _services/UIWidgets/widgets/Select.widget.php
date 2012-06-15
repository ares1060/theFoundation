<?php
	class UIW_Select extends AUIWidget {
	
		private $name;
		private $multiselect;
		private $label;
		private $id;
		private $size;
		private $type;
		private $options;
		
		function __construct(){
			$this->label = '';
			$this->size = 1;
			$this->type = 'dropdown';
			$this->multiselect = false;
			$this->options = array();
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

        public function setSize($size){
        	$this->size = $size;
        	if($size == 1){
        		$this->type = 'dropdown';
        	} else {
        		$this->type = 'list';
        	}
        }
        
        public function enableMultiselect(){
        	$this->multiselect = true;
        }
        
	    public function disableMultiselect(){
        	$this->multiselect = false;
        }
        
		public function addOption($caption, $value, $selected = false){
			$this->options[count($this->options)] = array('caption' => $caption, 'value' => $value, 'selected' => $selected);
		}
		
		/**
		 * Adds one ore move Options to the Dropdown
		 * @param unknown_type $options | ['caption'=>caption, 'value'=>value, OPT'selected'][..]
		 */
		public function addOptions($options){
			foreach($options as $opt){
				if(isset($opt['caption']) && isset($opt['value'])) $this->addOption($opt['caption'], $opt['value'], isset($opt['selected']));
			}
		}
		
		public function render() {
			$vd = new ViewDescriptor(AUIWidget::TPL_ROOT.'select');
			$vd->addValue('id', $this->id);
			$vd->addValue('name', $this->name);
			$vd->addValue('type', $this->type);
			$vd->addValue('multiple', (($this->multiselect)?'multiple="multiple"':''));
			$vd->addValue('size', $this->size);
			
			if($this->label != ''){
				$lvd = new SubViewDescriptor('label');
				$lvd->addValue('label', $this->label);
				$lvd->addValue('type', $this->type);
				$lvd->addValue('id', $this->id);
				$vd->addSubView($lvd);
			}
			
			$oc = count($this->options);
			for($o = 0; $o < $oc; $o++){
				$option = $this->options[$o];
				$ovd = new SubViewDescriptor('option');
				$ovd->addValue('caption', $option['caption']);
				$ovd->addValue('value', $option['value']);
				$ovd->addValue('selected', (($option['selected'])?'selected="selected"':''));
				$ovd->addValue('type', $this->type);
				$vd->addSubView($ovd);
			}
			
			return $vd->render();
		}
	
	}
?>