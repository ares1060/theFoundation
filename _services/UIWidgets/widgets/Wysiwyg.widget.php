<?php
	class UIW_Wysiwyg extends AUIWidget {
	
		private $name;
		private $value;
		private $label;
		private $id;
		private $rows;
	
		private $features;
		private $img;
		private $service;
		private $param;
		
		function __construct(){
			$this->label = '';
			$this->rows = 3;
			$this->features = array('bold','italic','underline','strikeThrough','subscript','superscript','html', 'left', 'center', 'right', 'justify', 'ol', 'ul', 'indent', 'outdent', 'hr', 'fontFormat', 'link','unlink', 'tables');
			$this->img = false;
			$this->img_album = '';
			$this->img_folder = '';
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

        public function setRows($rows){
        	$this->rows = $rows;
        }
        
        public function setAddImages($service, $param){
        	$this->img = true;
        	$this->service = $service;
        	$this->param = $param;
        }
		
		public function render() {
			$GLOBALS['extra_css'][] = 'services/uiwidgets_wysiwyg.css';
			$GLOBALS['extra_js'][] = 'uiwidgets_wysiwyg.js';
// 			$GLOBALS['extra_js'][] = 'nicEdit.js';
				
			$vd = new ViewDescriptor(AUIWidget::TPL_ROOT.'wysiwyg');
			$vd->addValue('id', $this->id);
			$vd->addValue('name', $this->name);
			$vd->addValue('value', $this->value);
			$vd->addValue('rows', $this->rows);
			
			/*if($this->img){
				$this->features[] = 'myimage';
				$s = new SubViewDescriptor('addImages');
				
				$s->addValue('service', $this->service);
				$s->addValue('param', $this->param);
				
				$vd->addSubView($s);
				unset($s);
			}*/
			
			$features = array();
			foreach($this->features as $f) $features[] = '\''.$f.'\'';

			$vd->addValue('features', implode(', ', $features));
			
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