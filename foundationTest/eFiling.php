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
   
    
	/* start with Template */
    $main = new ViewDescriptor('main');
    $content = new ViewDescriptor('eFiling');
    
    $chapter = (isset($_GET['chapter'])) ? $_GET['chapter'] : '';
    switch($chapter) {
    	case '':
    		$content->showSubView('home');
    		break;
    	case 'form':
    		if($_POST != array()) $sp->ref('eFiling')->executeNewFiling();
    		
    		$s = new SubViewDescriptor('form');
    		$s->addValue('id', $_GET['form']);
    		$content->addSubView($s);
    		unset($s);
    		break;
    	case 'thanks':
    		$s = new SubViewDescriptor('thanks');
    		$s->addValue('hash', $_GET['hash']);
    		$content->addSubView($s);
    		unset($s);
    		break;
    	case 'abmeldung':
    		$content->showSubView('abmeldung');
    		break;
    }
    
    
	$main->addValue('content', $content->render());
	$main->addValue('mainMenu_6', 'class="active"');
	
	/* Standard Replaces */
    echo $main->render();
?>