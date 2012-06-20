<?php
	/* Main includes (init the foundation) */
	$to_root = '../';
    require_once($to_root.'_core/theFoundation.php');
    
     /**
	 * HINTS:
	 * 	.) AUTHORIZATION FOR PAGE:
	 * 		$authorized ... Array |Êwill be checked in theFoundation.php
	 * 		insert authorized user-groups
	 * 		if $authorized = array() anyone will be authorized to view the page12
	 *  
	 *  .) CSS and JS to header:
	 *  	use $GLOBALS['extra_css'] and $GLOBALS['extra_js'] in Template to add extra CSS and JS to the header
	 */

    /* create new ServiceProvider */
    /* $sp = new ServiceProvider(); -- will be created in theFoundation.php*/
   
	// has to be logged in
    if($sp->ref('User')->isLoggedIn()){
    	
   		$sp->ref('Template')->setTemplate('adminTemplate2');
    	echo $sp->ref('Admincenter')->tplAdmin();
    	
    } else header('Location: '.$GLOBALS['abs_root'].'_admincenter/login/');
?>