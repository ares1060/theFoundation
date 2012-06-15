<?php
	/* Main includes (init the foundation) */
	$to_root = '../';
    $authorized = array();
    $connector = true;
    
    /*$GLOBALS['connector_to_root'] = (isset($_POST['to_root'])) ? $_POST['to_root'] : $to_root; // possibility to set new root if ajax file is in other folder
    $GLOBALS['connector_to_root'] = (isset($_GET['to_root'])) ? $_GET['to_root'] : $GLOBALS['connector_to_root'];*/
    
    error_reporting(E_ALL ^ E_NOTICE);
    
	require_once($to_root.'_core/theFoundation.php');
	
	ini_set("memory_limit","128M");
	
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
    $sp = new ServiceProvider();
    
    $service = isset($_GET['service']) ? $_GET['service'] : 'Image';
  /* 	$path = isset($_GET['path']) ? $_GET['path'] : '';
   	$height = isset($_GET['height']) ? $_GET['height'] : -1;
   	$width = isset($_GET['width']) ? $_GET['width'] : -1;*/
   	
    $sp->ref($service)->data($_GET);	
   
?>