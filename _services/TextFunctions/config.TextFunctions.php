<?php
	//$this->config['smilies_path'] = ((isset($GLOBALS['connector_to_root'])) ? $GLOBALS['connector_to_root']: $GLOBALS['to_root']).'_uploads/smilies/';
	$this->config['smilies_path'] = $GLOBALS['abs_root'].'_uploads/smilies/';
	
    $this->config['loc_folder'] = $GLOBALS['config']['root'].'/_services/TextFunctions/lang/';
    
    $this->config['show_date_long_ago'] = true; // will show the date instead of 10 years ago
    
    $this->config['default_rimg_preg'] = '/(?P<h>\d+):(?P<min>\d+):(?P<s>\d+) \| (?P<d>\d+).(?P<m>\d+).(?P<y>\d+)/';
    
    $this->config['mailcheck_dns'] = false;
?>