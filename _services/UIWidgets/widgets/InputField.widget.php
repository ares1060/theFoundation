<?php
	require_once $GLOBALS['config']['root'].'_services/UIWidgets/widgets/Input.widget.php';

	class UIW_InputField extends Input {
	
		function __construct(){
			parent::__construct('text');
		}
	
	}
?>