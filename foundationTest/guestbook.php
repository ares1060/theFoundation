<?php
	/* Main includes (init the foundation) */
	$to_root = '../';
    require_once($to_root.'_core/theFoundation.php');
    
     /**
	 * HINTS:
	 * 	.) AUTHORIZATION FOR PAGE:
	 * 		$authorized ... Array | will be checked in theFoundation.php
	 * 		insert authorized user-groups
	 * 		if $authorized = array() anyone will be authorized to view the page
	 * 
	 *  .) MESSAGES:
	 *  	msg service -> will be added to the page further down 
	 *  	@see: _core/Messages/Messages.php
	 *  
	 *  .) CSS and JS to header:
	 *  	use $GLOBALS['extracss'] and $GLOBALS['extrajs'] in Template to add extra CSS and JS to the header
	 */
    
    /* create new ServiceProvider */
    //$sp = new ServiceProvider();
    
    print_r($_POST);
    if(isset($_POST['gb_author'])) $sp->ref('Guestbook')->addEntry(array('type'=>'post'));
    
    /* start with Template */
    $main = new ViewDescriptor('main');

    $gb = new ViewDescriptor('guestbook');
    
	$main->addValue('content', $gb->render());
	$main->addValue('mainMenu_2', 'class="active"');
	
	/* Standard Replaces */
    echo $main->render();
?>