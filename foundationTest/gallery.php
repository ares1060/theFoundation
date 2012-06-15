<?php
	/* Main includes (init the foundation) */
	$to_root = '../';
    require_once($to_root.'_core/theFoundation.php');
    
     /**
	 * HINTS:
	 * 	.) AUTHORIZATION FOR PAGE:
	 * 		$authorized ... Array |Êwill be checked in theFoundation.php
	 * 		insert authorized user-groups
	 * 		if $authorized = array() anyone will be authorized to view the page
	 * 
	 *  .) MESSAGES:
	 *  	msg service -> will be added to the page further down 
	 *  	@see: _core/Messages/Messages.php
	 *  
	 *  .) CSS and JS to header:
	 *  	use $GLOBALS['extra_css'] and $GLOBALS['extra_js'] in Template to add extra CSS and JS to the header
	 */
    
    /* create new ServiceProvider */
    //$sp = new ServiceProvider();
    
    /* start with Template */
    $main = new ViewDescriptor('main');
    
    $gallery = new ViewDescriptor('gallery');
    
    $GLOBALS['extra_css'][] = 'gallery.css';
    $GLOBALS['extra_js'][] = 'jquery.address-1.4.min.js';
    $GLOBALS['extra_js'][] = 'gallery.js';
    
	$main->addValue('content', $gallery->render());
	$main->addValue('mainMenu_4', 'class="active"');
	
	/* Standard Replaces */
    echo $main->render();
?>