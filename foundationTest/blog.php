<?php
$time = microtime();
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

    /* handle _GET params */
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $mode = isset($_GET['mode']) ? $_GET['mode'] : '';
    $cat = isset($_GET['cat']) ? $_GET['cat'] : '';
    $tag = isset($_GET['tag']) ? $_GET['tag'] : '';
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    
    /* handle _POST params */
    //Comments
    $sp->ref('Comment')->checkNewComment();
    
    /* start with Template */
    $main = new ViewDescriptor('main');
    
    $blog = new ViewDescriptor('blog');
    $blog->addValue('page', $page);
    $blog->addValue('mode', $mode);
    $blog->addValue('cat', $cat);
    $blog->addValue('tag', $tag);
    $blog->addValue('id', $id);
    
	$main->addValue('content', $blog->render());
	$main->addValue('mainMenu_1', 'class="active"');
	
	/* Standard Replaces */

    echo $main->render();
    $diff = microtime() - $time;
    echo $diff;
?>