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
    $sp->ref('Newsletter')->handlePost();
    
    if(isset($_GET['demo_send'])){
    	$sp->ref('Newsletter')->sendNewsletter('default', 'testMail');
    } else if(isset($_GET['unsubscribe'])){
    	$sp->ref('Newsletter')->unregister($_GET['unsubscribe']);
    }
    
    /* start with Template */
    $main = new ViewDescriptor('main');
    
    $newsletter = new ViewDescriptor('newsletter');
    
	$main->addValue('content', $newsletter->render());
	$main->addValue('mainMenu_5', 'class="active"');
	
	/* Standard Replaces */
    echo $main->render();
?>