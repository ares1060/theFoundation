<?php
    $this->config['tpl']['login_form'] = '_core/user/login_form';
    $this->config['tpl']['ua_not_loggedin'] = '_core/user/ua_not_loggedin';
    $this->config['tpl']['ua_loggedin'] = '_core/user/ua_loggedin';
    
    $this->config['min_pwd_strength'] = 3; // 1-10 @see TextFunctions->getPasswordStrength()
    
    //$this->config['salt']['pwd'] = 'humptydumptysatonawall'; // will be generated dynamicly
    //$this->config['salt']['register_code'] = 'humptydumptyhadagreatfall'; // will be generated dynamicly
	
    $this->config['user_image_default'] = '/img/user/no_image.png';
    $this->config['user_image_default_id'] = 1;
    $this->config['max_file_size'] = 3145728; //3MB    
    $this->config['UserImage_data_id'] = 3; // will be written to database like that at setup()  

    // session
    $this->config['session']['regenerate_after'] = 900; //seconds | -1 = never
    $this->config['session']['idle_time'] = 3600; //seconds | 1800=30min |Ê3600 = 1h
    
    
    $this->config['loc_file'] = $GLOBALS['config']['root'].'_localization/core.user.loc.php';

    $this->config['user_per_page'] = 20;
    $this->config['usergroup_per_page'] = 20;
    $this->config['userdata_per_page'] = 20;
    
   	$this->config['tpl_root'] = '_core/user/admin/';
    $this->config['tpl']['Usercenter']['main'] = $this->config['tpl_root'].'usercenter';
    
    $this->config['tpl']['Usercenter']['user'] = $this->config['tpl_root'].'user_main';
    $this->config['tpl']['Usercenter']['edit_user'] = $this->config['tpl_root'].'user_edit';
    $this->config['tpl']['Usercenter']['new_user'] = $this->config['tpl_root'].'user_new';
    
    $this->config['tpl']['Usercenter']['usergroup'] = $this->config['tpl_root'].'usergroup_main';
    $this->config['tpl']['Usercenter']['usergroup_edit'] = $this->config['tpl_root'].'usergroup_edit';
    $this->config['tpl']['Usercenter']['usergroup_new'] = $this->config['tpl_root'].'usergroup_new';
    
    $this->config['tpl']['Usercenter']['userdata'] = $this->config['tpl_root'].'userdata_main';
    $this->config['tpl']['Usercenter']['userdata_edit'] = $this->config['tpl_root'].'userdata_edit';
    $this->config['tpl']['Usercenter']['userdata_new'] = $this->config['tpl_root'].'userdata_new';

    
?>