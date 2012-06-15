<?php
	/* Main includes (init the foundation) */
	$to_root = '../../';
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
    
    
    
    $chap = isset($_GET['chap']) ? $_GET['chap'] : '';
    
    switch($chap){
    	case 'template':
    		$doc = new ViewDescriptor('documentation');
    		$doc->addValue('selected_0_0', 'class="selected"');
    		break;
    	case 'filesystem':
    		$doc = new ViewDescriptor('documentation');
    		$doc->addValue('selected_0_1', 'class="selected"');
    		break;
    	default:
    		$doc = new ViewDescriptor('documentation');
    		$doc->addValue('selected_0', 'class="selected"');
    		break;
    }
    $doc->showSubView('basics');
    
	$main->addValue('mainMenu_3', 'class="active"');
	$main->addValue('content', $doc->render());
	
	/* Standard Replaces */
    $GLOBALS['extra_css'][] = 'documentation.css';
    echo $main->render();
?>