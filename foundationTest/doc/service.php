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
    
    $doc = new ViewDescriptor('documentation');
   	
    $chap = isset($_GET['chap']) ? $_GET['chap'] : '';
    
    if($chap != ''){
   		if(is_file($GLOBALS['config']['root'].'_template/_doc/services/service.'.$chap.'.html')){
    		$cont = new ViewDescriptor('../../_doc/services/service.'.$chap);
   		} else $cont = new ViewDescriptor('../../_doc/services/no_service');
    } else $cont = new ViewDescriptor('../../_doc/services/choose_service');
    $got = false;
	//get Service docs
	$folder = $to_root.'_services';
	if ($handle = opendir($folder)) {
		while (false !== ($file = readdir($handle))) {
			if(substr($file, 0, 1) !== '.') {
				$s = new SubViewDescriptor('service');
				$s->addValue('name', $file);
				$s->addValue('webname', strtolower($file));
				if((strtolower($file) == $chap)){
					$sel = 'class="selected"';
					$got = true;
				} else $sel = '';
				$s->addValue('selected', $sel);
				$doc->addSubView($s);
				unset($s);
			}
		}
	}
    if(!$got) $doc->addValue('selected_2', 'class="selected"');
	$doc->addValue('subcontent', $cont->render());
		
	$main->addValue('mainMenu_3', 'class="active"');
	$main->addValue('content', $doc->render());
	
	/* Standard Replaces */
    $GLOBALS['extra_css'][] = 'documentation.css';
    echo $main->render();
?>