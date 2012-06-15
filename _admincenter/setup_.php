<?php
	/* Main includes (init the foundation) */
	$to_root = '../';
	$GLOBALS['setup'] = true;
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
	
   	//$_GET['chapter'] = (!isset($_GET['chapter']) && $sp->db->data(array('query'=>'SHOW TABLES like "'.$GLOBALS['db']['db_prefix'].'rights"', 'type'=>'row')) !== false) ? '-1' : $_GET['chapter'];
   	if(isset($_GET['chapter']) && $_GET['chapter'] == '2' && isset($_POST['db_pre'])){
   		if($sp->db->data(array('query'=>'SHOW TABLES like "'.$_POST['db_pre'].'rights"', 'type'=>'row')) === false){
   			$_GET['chapter'] = '3'; 
   		}
   	} 
   		
   	$page = isset($_GET['chapter']) ? $_GET['chapter'] : '-1';
   	
	$tpl = new ViewDescriptor('setup');
	
	switch($page){
		case '-1':
			$tpl->showSubView('inst_exists');
			break;
		case '1':
			// first page (db)
			$s = new SubViewDescriptor('db');
			$s->addValue('host', $GLOBALS['db']['database_host']);
			$s->addValue('user', $GLOBALS['db']['database_user']);
			$s->addValue('pwd', $GLOBALS['db']['database_pwd']);
			$s->addValue('table', $GLOBALS['db']['database_table']);
			$s->addValue('pre', $GLOBALS['db']['db_prefix']);
			$tpl->addSubView($s);
			unset($s);
			
			break;
		case '2':
			$s = new SubViewDescriptor('prefix_exists');
			$tpl->addSubView($s);
			unset($s);
			break;
		case '3':
			saveDbConstants($sp);
			
			$sp->ref('Database')->reloadConfig();
			$s = new SubViewDescriptor('setup');
			
			$sp->ref('Database')->reloadConfig();
			
			// delete caches
			/*if(deleteDataInFolder($to_root.'_cache/template') && deleteDataInFolder($to_root.'_cache/images')) $s->showSubView('del_cache_success');
			else  $s->showSubView('del_cache_error');
			if(deleteDataInFolder($to_root.'_uploads/ftp_tmp/') && deleteDataInFolder($to_root.'_uploads/gallery/')) $s->showSubView('del_upload_success');
			else  $s->showSubView('del_upload_error');*/
			
			$s->showSubView('del_cache_error');
			$s->showSubView('del_upload_error');
			$se = '';
			
			$array = array('Serviceprovider'=>$sp,
						'Datenbank'=>$sp->ref('Database'),
						'Rechtemanagement'=>$sp->ref('Rights'),
						'Usersystem'=>$sp->ref('User'),
						'Lokalisation'=>$sp->ref('Localization'),
						'Templatesystem'=>$sp->ref('Template'),
						'Infosystem'=>$sp->ref('Messages'),
						'Admincenter'=>$sp->ref('Admincenter'));
			
			foreach($array as $k=>$v){
				$s1 = new SubViewDescriptor('main');
				$s1->addValue('name', $k);
				if($v->setup()) $s1->showSubView('m_success');
				else $s1->showSubView('m_error');
				
				$s->addSubView($s1);
				unset($s1);
			}
			$ar = $sp->ref('Admincenter')->installActivatedServices();
			foreach($ar as $k=>$v){
				$s1 = new SubViewDescriptor('services');
				$s1->addValue('name', $k);
				if($v) $s1->showSubView('s_success');
				else $s1->showSubView('s_error');
				
				$s->addSubView($s1);
				unset($s1);
			}
			
			$tpl->addSubView($s);
			break;
		case '4':
			$s = new SubViewDescriptor('pot');
			
			$ar = $sp->ref('Admincenter')->getActiveServices();
			
			foreach($ar as $service){
				$s1 = new SubViewDescriptor('services');
				
				$s1->addValue('id', $service['id']);
				$s1->addValue('name', $service['name']);
				
				if($sp->ref('Localization')->hasPOTFile($service['class'])) $s1->showSubView('m_exists');
				else $s1->showSubView('m_not_exists');
				
				$s->addSubView($s1);
				unset($s1);
			}
			
			$tpl->addSubView($s);
			break;
	}
	
    echo $tpl->render();
    
    function saveDbConstants($sp) {
    	if(isset($_POST['db_host']) &&
    		isset($_POST['db_user']) &&
    		isset($_POST['db_password']) &&
    		isset($_POST['db_table']) &&
    		isset($_POST['db_pre'])) {
    		
    			$output = "<?php \r\n
	\t//Database Constanten
	\t\$GLOBALS['db']['database_host'] = '".$_POST['db_host']."';
	\t\$GLOBALS['db']['database_user'] = '".$_POST['db_user']."';
	\t\$GLOBALS['db']['database_pwd'] = '".$_POST['db_password']."';
	\t\$GLOBALS['db']['database_table'] = '".$_POST['db_table']."';
    					
	\t\$GLOBALS['db']['db_prefix'] = '".$_POST['db_pre']."';
	\t?>";
    			$sp->ref('Filehandler')->view(array('file'=>$GLOBALS['to_root'].'/_core/Database/Database.config.php', 'action'=>'write', 'data'=>$output));
    	}
    }
    
    function deleteDataInFolder($folder, $removeDir=false){
    	$error = array();
		foreach(glob($folder.'*.*') as $v){
			if(is_dir($v) && substr($v, 0, 1) != '.') deleteDataInFolder($v, true);
			else if(is_file($v)) $error[] = !unlink($v);
		}
		if($removeDir) rmdir($folder);
		return !in_array('true', $error);
    }
?>