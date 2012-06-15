<?php
	require_once $GLOBALS['config']['root'].'_services/UIWidgets/widgets/Input.widget.php';

	class UIW_FileUploadInput extends Input {
	
		function __construct(){
			parent::__construct('file');
		}
	
	}
?>