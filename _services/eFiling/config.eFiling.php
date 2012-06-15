<?php
    $this->config['per_page']['admin']['forms'] = 20;
    $this->config['per_page']['admin']['groups'] = 20;
    $this->config['per_page']['admin']['filings'] = 20;
    
    $this->config['standard_preview_replace'] = '{val} ';
    
    $this->config['tpl_root'] = '/_services/eFiling/';
    $this->config['loc_folder'] = $GLOBALS['config']['root'].$this->config['tpl_root'].'lang/';
    
    
    $this->config['tpl']['front/datagroup'] = $this->config['tpl_root'].'front/datagroup';
    $this->config['tpl']['front/datagroup_confirm'] = $this->config['tpl_root'].'front/datagroup_confirm';
    $this->config['tpl']['front/form'] = $this->config['tpl_root'].'front/form';
    $this->config['tpl']['front/thanks'] = $this->config['tpl_root'].'front/thanks';
    
    /* ----- admin ----- */
    $this->config['tpl']['admin/main'] = $this->config['tpl_root'].'admin/main';
    $this->config['tpl']['admin/filings'] = $this->config['tpl_root'].'admin/filings';
    $this->config['tpl']['admin/edit_filing'] = $this->config['tpl_root'].'admin/edit_filing';
    
    $this->config['tpl']['admin/groups'] = $this->config['tpl_root'].'admin/groups';
    $this->config['tpl']['admin/edit_group'] = $this->config['tpl_root'].'admin/edit_group';
    $this->config['tpl']['admin/new_group'] = $this->config['tpl_root'].'admin/new_group';
    $this->config['tpl']['admin/available_groups'] = $this->config['tpl_root'].'admin/available_groups';
    
    $this->config['tpl']['admin/forms'] = $this->config['tpl_root'].'admin/forms';
    $this->config['tpl']['admin/new_form'] = $this->config['tpl_root'].'admin/new_form';
    $this->config['tpl']['admin/edit_form'] = $this->config['tpl_root'].'admin/edit_form';

    $this->config['tpl']['admin/data'] = $this->config['tpl_root'].'admin/data';
    $this->config['tpl']['admin/edit_data'] = $this->config['tpl_root'].'admin/edit_data';
    $this->config['tpl']['admin/new_data'] = $this->config['tpl_root'].'admin/new_data';
    
    
    //$this->config['loc_file'] = $GLOBALS['config']['root'].'/_localization/eFiling.loc.php';
?>