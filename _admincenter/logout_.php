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
	 *  	use $GLOBALS['extracss'] and $GLOBALS['extrajs'] in Template to add extra CSS and JS to the header
	 */
    
    /* create new ServiceProvider */
    //$sp = new ServiceProvider();
    
    if(isset($_GET['action']) && $_GET['action']=='logout') {
        if($sp->run('User', array('action'=>'logout'))){
        	$sp->run('Messages', array('message'=>$sp->loc->data(array('str'=>'LOGOUT_SUCCESS', 'service'=>'user')), 'type'=>Messages::INFO));
        } else {
        	$sp->run('Messages', array('message'=>$sp->loc->data(array('str'=>'LOGOUT_ERROR', 'service'=>'user')), 'type'=>Messages::ERROR));
        }
        header('Location: '.$GLOBALS['abs_root']);
        
    }


?>