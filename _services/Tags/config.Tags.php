<?php 
	//$this->config['loc_folder'] = $GLOBALS['config']['root'].'/_services/Tags/lang/';
	
	$this->config['max_tag_cloud_size'] = 9;

	$this->config['tpl_root'] = '/_services/Tags/';
	$this->config['tpl']['service_tags'] = $this->config['tpl_root'].'service_tags';
	$this->config['tpl']['service_tag_cloud'] = $this->config['tpl_root'].'service_tag_cloud';

	$this->config['tpl']['service_admin_tags'] = $this->config['tpl_root'].'service_admin_tags';
	
?>