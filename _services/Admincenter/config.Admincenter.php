<?php
	/* -- site config -- */
	$this->config['site_title'] = 'Moonshine - Internet destillate';
	
	$this->config['show_defaultPwd_alert'] = false;

    /* template */
    $this->config['tpl_root'] = '/_services/Admincenter/';
	$this->config['admincenter']['main'] = $this->config['tpl_root'].'main';
	
	/* -- till db service management works -- */
	$this->config['activated_services'] = array('gallery', 'efiling', 'shop', 'user');
	$this->config['setup_services'] = array('gallery', 'efiling', 'tags', 'category', 'shop');
	return $this->config['services'] = array(
			array('id'=>1, 'display'=>'Blog', 'name'=>'blog', 'image'=>'blog_big.png', 'class'=>'Blog', 'config_hash'=>''),
			array('id'=>2, 'display'=>'Galerie', 'name'=>'gallery', 'image'=>'gallery_big_1.png', 'class'=>'Gallery', 'config_hash'=>''),
			array('id'=>5, 'display'=>'G&auml;stebuch', 'name'=>'guestbook', 'image'=>'guestbook_big.png', 'class'=>'Guestbook', 'config_hash'=>''),
			array('id'=>6, 'display'=>'Kommentare', 'name'=>'comments', 'image'=>'comments_big.png', 'class'=>'Comment', 'config_hash'=>''),
			array('id'=>7, 'display'=>'Rating', 'name'=>'rating', 'image'=>'rating_big.png', 'class'=>'Rating', 'config_hash'=>''),
			array('id'=>9, 'display'=>'eFiling', 'name'=>'efiling', 'image'=>'efiling_big.png', 'class'=>'eFiling', 'config_hash'=>''),
			array('id'=>11, 'display'=>'Tags', 'name'=>'tags', 'image'=>'tags_big.png', 'class'=>'Tags', 'config_hash'=>''),
			array('id'=>12, 'display'=>'Category', 'name'=>'category', 'image'=>'category_big.png', 'class'=>'Category', 'config_hash'=>''),
			array('id'=>10, 'display'=>'Shop', 'name'=>'shop', 'image'=>'shop_big.png', 'class'=>'Shop', 'config_hash'=>'6177c69fdafd48052b385b0057639bf0'),
			array('id'=>4, 'display'=>'User', 'name'=>'user', 'image'=>'user_big.png', 'class'=>'User', 'config_hash'=>''));
?>