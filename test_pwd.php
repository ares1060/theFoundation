<?php
	/* Main includes (init the foundation) */
	$to_root = '';
    require_once($to_root.'_core/theFoundation.php');

    $pwd = isset($_GET['pwd']) ? $_GET['pwd'] : '';
	$hash = isset($_GET['hash']) ? $_GET['hash'] : '';
	$old = isset($_GET['old']) ? $_GET['old'] : '';
	
	if($pwd != '' && $hash != '') {
		
		echo 'Hash: <br />'.$sp->ref('TextFunctions')->hashString($pwd, $hash, 'whirlpool').'<br />';
		if($old != '') echo $old.'#'.$hash;
	} else echo 'wrong params';
?>