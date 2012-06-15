<?php
	$this->config['per_page']['admin']['products'] = 20;

	$this->config['stock_full_treshold'] = 5;
	$this->config['product_category_style'] = 'divs';
	 
	$this->config['gallery_album_id'] = 3; // 67
	
	$this->config['tpl_root'] = '_services/Shop/';

	/** view **/
	$this->config['tpl']['view/categories'] =  $this->config['tpl_root'].'view/categories';
	$this->config['tpl']['view/product'] =  $this->config['tpl_root'].'view/product';
	$this->config['tpl']['view/tag'] =  $this->config['tpl_root'].'view/tag';
	
	$this->config['tpl']['view/cart_small'] =  $this->config['tpl_root'].'view/cart_small';
	$this->config['tpl']['view/cart'] =  $this->config['tpl_root'].'view/cart';
	
	/** admin **/
    $this->config['tpl']['admin/admincenter'] =  $this->config['tpl_root'].'admin/admincenter';
    $this->config['tpl']['admin/products_overview'] =  $this->config['tpl_root'].'admin/products_overview';
    $this->config['tpl']['admin/product_new'] =  $this->config['tpl_root'].'admin/product_new';
    $this->config['tpl']['admin/product_edit'] =  $this->config['tpl_root'].'admin/product_edit';
?>