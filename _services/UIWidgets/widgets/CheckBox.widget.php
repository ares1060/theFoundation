<?php
	require_once $GLOBALS['config']['root'].'_services/UIWidgets/widgets/Input.widget.php';

	class UIW_CheckBox extends Input {
	
		private $checked = false;
		
		function __construct(){
			parent::__construct('checkbox');
		}
		
		function setChecked(){
			$this->checked = true;
		}
		
		public function preRender($vd) {
			if($this->checked) $vd->addValue('checked', 'checked="checked"');
			return $vd;
		}
	
	}
?>