<?php
	session_start();
	
	//if(isset($_SESSION['User'])) error_log('USER');
	
	if(empty($_SERVER['REQUEST_URI'])) {
	    $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
	}
	
    $GLOBALS['stat']['start'] = microtime(true);
	$GLOBALS['config']['default_language'] = 'de';
	$GLOBALS['config']['root'] = substr(dirname(__FILE__), 0, -5);	
	$GLOBALS['to_root'] = isset($to_root) ? $to_root : '';
	
	//get abs link to main
	$a = explode('/', $_SERVER['PHP_SELF']);
	array_pop($a);
	$folder = implode('/', $a).'/';
	$GLOBALS['abs_root'] = 'http://'.$_SERVER['HTTP_HOST'].$folder.$GLOBALS['to_root'];
	$GLOBALS['working_dir'] = 'spidernet/';
	$GLOBALS['testDatabase'] = true; // if true the services databases will be deleted before install
	
	$root = '';
	for($i=0;$i<((count(explode('/', $GLOBALS['config']['root']))-2)-(count(explode('/', $_SERVER['REQUEST_URI']))-2)-(count(explode('/', $_SERVER['DOCUMENT_ROOT']))-1))*(-1);$i++){
		$root .= '../';
	}
    
	$GLOBALS['config']['login'] = $GLOBALS['abs_root'].'_admincenter/login/';
	$GLOBALS['tpl']['root'] = '';
    
	$GLOBALS['extra_css'] = array();
	$GLOBALS['extra_js'] = array();
	
	/* -- Save active and previous page in session -- */
	if(!isset($connector) || !$connector){
		if(!isset($_SESSION['history']['prev_page'])) $_SESSION['history']['prev_page'] = $to_root.'';
		if(isset($_SESSION['history']['active_page'])){
			if(isset($_SESSION['history']['prev_page']) && $_SESSION['history']['prev_page'] != $_SESSION['history']['active_page']) $_SESSION['history']['prev_page'] = $_SESSION['history']['active_page'];
		} else {
			$_SESSION['history']['prev_page'] = 'index.php';
		}
		$get = '';
		foreach($_GET as $k=>$g){
			$w = ($get == '') ? '?' : '&';
			$get .= $w.$k.'='.$g;
		}
		$_SESSION['history']['active_page'] = (substr($_SERVER['SCRIPT_FILENAME'], strlen($GLOBALS['config']['root']), strlen($_SERVER['SCRIPT_FILENAME'])-strlen($GLOBALS['config']['root']))).$get;
	}
	
	require_once($GLOBALS['config']['root'].'_core/_serviceprovider/TFCoreFunctions.php');
	require_once($GLOBALS['config']['root'].'_core/_serviceprovider/Service.php');
	require_once($GLOBALS['config']['root'].'_core/_serviceprovider/IService.php');
	require_once($GLOBALS['config']['root'].'_core/_serviceprovider/ServiceProvider.php');
	require_once($GLOBALS['config']['root'].'_core/Template/ViewDescriptor.php');
	require_once($GLOBALS['config']['root'].'_core/Template/SubViewDescriptor.php');
		
	$sp = new ServiceProvider();

	/* check session expiration */
	$sp->ref('User')->checkSessionExpiration();
	//print_r($_SESSION);
	/* check authorization */
	if(isset($authorized) && is_array($authorized) && $authorized != array()) {
		$gr = ($sp->ref('User')->isLoggedIn()) ? $sp->ref('User')->getLoggedInUser()->getGroup()->getId() : -1;
		error_log('sdf');
		if(!in_array(strtolower(User::getUserGroupNameFromId($gr)), $authorized) && !in_array($gr, $authorized)) {header('Location: '.$GLOBALS['config']['login']); exit(0);}
	}
?>