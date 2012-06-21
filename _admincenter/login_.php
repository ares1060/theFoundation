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
   	$sp->ref('Template')->setTemplate('adminTemplate');

   	/* redirect loggedin Users */
    if(isset($_POST['u_nick']) && isset($_POST['u_pwd'])) {
        if($sp->run('User', array('action'=>'login', 'nick'=>$_POST['u_nick'], 'pwd'=>$_POST['u_pwd']))){
        	header('Location: ../');
        	exit(0);
        } 
    }
    
    /* start with Template */
    $main = new ViewDescriptor('login');    

    /* Standard Replaces */
    echo $main->render();
?>