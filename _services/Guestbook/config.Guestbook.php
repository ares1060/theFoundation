<?php
    $this->config['perPage'] = 10;
    $this->config['checkNewMessages'] = false;
    $this->config['captcha'] = false;
    
    $this->config['tpl_root'] = '/_services/Guestbook/';
    $this->config['tpl']['form'] = $this->config['tpl_root'].'form';
    $this->config['tpl']['main'] = $this->config['tpl_root'].'main';
    $this->config['tpl']['admin_main'] = $this->config['tpl_root'].'admin/main';
    $this->config['tpl']['admin_info'] = $this->config['tpl_root'].'admin/info';
    $this->config['tpl']['admin_admin'] = $this->config['tpl_root'].'admin/admin';
    $this->config['tpl']['admin_submenu'] = $this->config['tpl_root'].'submenu';
    
    $this->config['loc_file'] = $GLOBALS['config']['root'].'/_localization/Guestbook.loc.php';
?>