<?php
	$this->config['captcha'] = false;

	$this->config['tpl_root'] = '_services/Comment/';
    $this->config['tpl']['view/list'] = $this->config['tpl_root'].'list';
    $this->config['tpl']['view/form'] = $this->config['tpl_root'].'form';

    $this->config['loc_file'] = $GLOBALS['config']['root'].'/_localization/Comment.loc.php';

    $this->config['css_file'] = 'services/comments.css';
    
    $this->config['per_page_list'] = 5;
?>