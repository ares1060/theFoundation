<?php
	class UIW_Table extends AUIWidget {
	
		private $rows;
		private $head;
		private $id;
	
		function __construct(){
            $this->rows = array();
        }
		
        public function setHead(array $values){
        	$this->head = $values;
        }
        
		public function setId($id){
        	$this->id = $id;
        }
        
		public function addRow(array $values){
			$this->rows[count($this->rows)] = $values;
		}
		
		public function render() {
			$vd = new ViewDescriptor(AUIWidget::TPL_ROOT.'table');
			$vd->addValue('id', $this->id);
			
			//build thead
			if(is_array($this->head)){
				$headSvd = new SubViewDescriptor('thead');
				$vd->addSubView($headSvd);
				$hc = count($this->head);
				for($h = 0; $h < $hc; $h++){
					$colSvd = new SubViewDescriptor('theadcol');
					$value = $this->parseValue(($this->head[$h] instanceof AUIWidget)?$this->head[$h]->render():$this->head[$h]);
					$colSvd->addValue('value', $value['value']);
					$colSvd->addValue('attributes', @$value['attributes']);
					$headSvd->addSubView($colSvd);
				}
			} else {
				$vd->removeSubView('thead');
			}
			
			//build tbody
			$rc = count($this->rows);
			for($r = 0; $r < $rc; $r++){
				$data = $this->rows[$r];
				$rowSvd = new SubViewDescriptor('row');
				$vd->addSubView($rowSvd);
				$cc = count($data);
				for($c = 0; $c < $cc; $c++){
					$colSvd = new SubViewDescriptor('col');
					$value = $this->parseValue(($data[$c] instanceof AUIWidget)?$data[$c]->render():$data[$c]);
					$colSvd->addValue('value', $value['value']);
					$colSvd->addValue('attributes', @$value['attributes']);
					$rowSvd->addSubView($colSvd);
				}
			}

			//render ViewDescriptor and return result
			return $vd->render();
		}
	
	}
?>