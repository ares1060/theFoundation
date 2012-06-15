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
    
    /* start with Template */
    $main = new ViewDescriptor('main');
    
    if(isset($_GET['generatePOT'])) {
    	$sp->ref('Localization')->generatePOTFile('TextFunctions');
    }
    
    $content= new ViewDescriptor('home');
    
	$content->addValue('test', $sp->ref('Tags')->tplGetTags('blog', 1));
	$content->addValue('test1', $sp->ref('Tags')->tplGetTagCloud('blog', 1));
	//$content->addValue('test2', $sp->ref('Category')->tplCategoryTree('Blog'));
	
	$main->addValue('content', $content->render());
	$main->addValue('mainMenu_0', 'class="active"');
	
	/* Standard Replaces */
    echo $main->render();
?>