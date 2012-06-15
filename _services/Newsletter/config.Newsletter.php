<?php
	$this->config['loc_file'] = $GLOBALS['config']['root'].'/_localization/Newsletter.loc.php';

	$this->config['unregister_salt'] = 'J>D-Yht?adN^/&{m}ENC@q*w^Iky}wqnqo@#ktL|=MDf|+.mh]';	
	
	$this->config['tpl_root'] = '_services/Newsletter/';
    $this->config['tpl']['register'] = $this->config['tpl_root'].'register';
    $this->config['tpl']['unregister'] = $this->config['tpl_root'].'unregister';
    $this->config['tpl']['mail'] = $this->config['tpl_root'].'mail';
    
    
    $this->config['replyTo'] = 'admin@jsgumpendorf.net';
    $this->config['domain'] = 'jsgumpendorf.net';
    
    $this->config['showMessageAfterSending'] = true;
?>