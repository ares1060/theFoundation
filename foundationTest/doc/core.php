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
    $sp = new ServiceProvider();
    
    /* start with Template */
    $main = new ViewDescriptor('main');
    
    $chap = isset($_GET['chap']) ? $_GET['chap'] : '';
    
    switch($chap){
    	case 'database':
    		$doc = new ViewDescriptor('documentation');
    		$cont = new ViewDescriptor('../../_doc/core/core.database');
    		$doc->addValue('subcontent', $cont->render());
    		$sel = 'selected_1_0';
    		break;
    	case 'localization':
    		$doc = new ViewDescriptor('documentation');
    		$sel = 'selected_1_1';
    		break;
    	case 'messages':
    		$doc = new ViewDescriptor('documentation');
    		$sel = 'selected_1_2';
    		break;
    	case 'template':
    		$doc = new ViewDescriptor('documentation');
    		$sel = 'selected_1_3';
    		break;
    	case 'user':
    		$doc = new ViewDescriptor('documentation');
    		$sel = 'selected_1_4';
    		break;
    	default:
    		$doc = new ViewDescriptor('documentation');
    		$sel = 'selected_1';
    		break;
    }
    $c = new SubViewDescriptor('core');
    $c->addValue($sel, 'class="selected"');
    $doc->addSubView($c);
    unset($c);
    $doc->addValue($sel, 'class="selected"');
    
	$main->addValue('mainMenu_3', 'class="active"');
	$main->addValue('content', $doc->render());
	
	/* Standard Replaces */
    $GLOBALS['extra_css'][] = 'documentation.css';
    echo $main->render();
?>