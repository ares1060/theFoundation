<?php
	class UIW_Form extends AUIWidget {
	
		private $items;
		private $action;
		private $method;
		private $enctype;
		private $id;
	
		function __construct(){
            $this->items = array();
            $this->method = 'post';
            $this->enctype = 'application/x-www-form-urlencoded';
        }
        
		public function setId($id){
        	$this->id = $id;
        }
        
		public function setAction($action){
        	$this->action = $action;
        }
        
		public function setEnctype($enctype){
        	$this->enctype = $enctype;
        }        
        
        public function configureAsFileUpload(){
        	$this->enctype = 'multipart/form-data';
        	$this->method = 'post';
        }
        
		public function addItem($item){
			$this->items[count($this->items)] = $item;
		}
		
		public function render() {
			$vd = new ViewDescriptor(AUIWidget::TPL_ROOT.'form');
			$vd->addValue('id', $this->id);
			$vd->addValue('action', $this->action);
			$vd->addValue('method', $this->method);
			$vd->addValue('enctype', $this->enctype);
			
			$content = '';

			$ic = count($this->items);
			for($i = 0; $i < $ic; $i++){
				$data = $this->items[$i];
				$content .= ($data instanceof AUIWidget)?$data->render():$data;
			}
			
			$vd->addValue('content', $content);
			
			return $vd->render();
		}
	
	}
?>