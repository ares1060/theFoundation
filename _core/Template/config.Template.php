<?php
    $this->config['base_template'] = 'basement';
    $this->config['template'] = 'foundationTest';
    if($this->config['template'] == '') $this->config['template'] = $this->config['base_template'];
	$this->config['loc_file'] = $GLOBALS['config']['root'].'_localization/core.template.loc.php';
	
	$this->config['cache_folder'] = '_cache/template';
	$this->config['cache_level'] = 0; //0 -> only at runtime; 1 -> php file cache; (2 -> cache rendered pages)
	
	$this->config['ajax_template_change'] = 2; // 0 = off; 1 = only when loggedin (adminTemplate) ; 2 = on
	
	$this->config['service_tag_mode'] = 2; //0 -> classic service tag parsing; 1 -> service tags params will be parsed as a json string; 2 -> parameter will be executed in right format automatically
?>