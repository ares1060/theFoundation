<?php
    $this->config['per_page_list'] = 2;
    $this->config['per_page_kategory'] = 10;
    $this->config['per_page_tag'] = 10;
    
    $this->config['per_page_list_admin'] = 20;
    $this->config['per_page_kategory_admin'] = 20;
    $this->config['per_page_tag_admin'] = 20;
    
    $this->config['tpl_root'] = '_services/Blog/';
    $this->config['tpl']['list/main'] = $this->config['tpl_root'].'list';
    $this->config['tpl']['list/cat'] = $this->config['tpl_root'].'cat';
    $this->config['tpl']['list/tags'] = $this->config['tpl_root'].'tags';
    $this->config['tpl']['view/main'] = $this->config['tpl_root'].'view';
    $this->config['tpl']['stuff/list_categories'] = $this->config['tpl_root'].'side_categories';
    $this->config['tpl']['stuff/list_tags'] = $this->config['tpl_root'].'side_tags';
    $this->config['tpl']['stuff/list_tag_cloud'] = $this->config['tpl_root'].'side_tag_cloud';
   
    $this->config['tpl']['admin'] = $this->config['tpl_root'].'admin/main';
    $this->config['tpl']['admin/list'] = $this->config['tpl_root'].'admin/list';
    $this->config['tpl']['admin/edit'] = $this->config['tpl_root'].'admin/edit';
    $this->config['tpl']['admin/new'] = $this->config['tpl_root'].'admin/new';
    
    $this->config['loc_file'] = $GLOBALS['config']['root'].'/_localization/Blog.loc.php';
   
    $this->config['css_file'] = 'services/blog.css';
?>