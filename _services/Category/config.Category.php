<?php
	$this->config['category_album_id'] = 2; // 67

	$this->config['tpl_root'] = '_services/Category/';
    
    $this->config['tpl']['admin/category_view_rec'] = $this->config['tpl_root'].'admin/category_rec'; 
    $this->config['tpl']['admin/categories_view'] = $this->config['tpl_root'].'admin/categories'; 
    
    $this->config['tpl']['admin/category_rec'] = $this->config['tpl_root'].'admin/category_admincenter_rec'; 
    $this->config['tpl']['admin/categories'] = $this->config['tpl_root'].'admin/categories_admincenter'; 
    $this->config['tpl']['admin/edit_category'] = $this->config['tpl_root'].'admin/edit_category'; 
    ?>