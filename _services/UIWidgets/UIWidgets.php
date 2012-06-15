<?php
	require_once $GLOBALS['config']['root'].'_services/UIWidgets/AUIWidget.php';

	class UIWidgets extends Service implements IService {
	
		protected $name = 'UIWidgets';
		
		function __construct(){
            parent::__construct();
            $this->loadConfig($GLOBALS['config']['root'].'_services/UIWidgets/config.UIWidgets.php');
            $this->sp->run('Localization', array('load'=>$this->config['loc_file']));
        }
        
        public function view($args){
            $GLOBALS['extra_js'][] = 'uiwidget.js';
            $GLOBALS['extra_css'][] = 'uiwidget.css';
            
            $type = (isset($args['type']) && $args['type'] != '') ? $args['type'] : '';
            if(isset($args['action'])){
            	if($args['action'] == 'FTPFiles'){
            		return $this->getFTPFiles($type);
            	}
            } else {
	            if(isset($args['widget'])){
	        		$i = $this->getWidget($args['widget']);
	        		if(isset($i) && $i != null){
		        		if(isset($args['id']) && method_exists($i, 'setId')) $i->setId($args['id']);
		        		if(isset($args['type']) && method_exists($i, 'setType')) $i->setType($args['type']);
		        		if(isset($args['value']) && method_exists($i, 'setValue')) $i->setValue($args['value']);
		        		if(isset($args['height']) && method_exists($i, 'setHeight')) $i->setHeight($args['height']);
		        		if(isset($args['name']) && method_exists($i, 'setName')) $i->setName($args['name']);
		        		if(isset($args['label']) && method_exists($i, 'setLabel')) $i->setLabel($args['label']);
		        		if(isset($args['rows']) && method_exists($i, 'setRows')) $i->setRows($args['rows']);
		        		if(isset($args['class']) && method_exists($i, 'setClass')) $i->setClass($args['class']);
		        		if(isset($args['style']) && method_exists($i, 'setStyle')) $i->setStyle($args['style']);
		        		/* -- extra args -- */
		        		if(isset($args['max_file_size']) && method_exists($i, 'setMaxFileSize')) $i->setMaxFileSize($args['max_file_size']); // for upload widget
		        		if(isset($args['max_uploads']) && method_exists($i, 'setMaxUploads')) $i->setMaxUploads($args['max_uploads']); // for upload widget
		        		if(isset($args['checked']) && method_exists($i, 'setChecked') && $args['checked']=='true') $i->setChecked();
		        		
		        		return $i->render();
		        		
	        		} else {
						$this->sp->msg->run(array('message'=>str_replace('{@pp:name}', $args['name'], $this->_('WIDGET_NOT_FOUND', 'UIWidgets')), 'type'=>Messages::DEBUG_ERROR));
	        			return str_replace(array('{@pp:name}'), array($args['widget']), $this->_('WIDGET_NOT_FOUND', 'UIWidgets'));
	        		}
	        	} else {
						$this->sp->msg->run(array('message'=>str_replace('{@pp:name}', $args['widget'], $this->_('NO_NAME_DEFINED', 'UIWidgets')), 'type'=>Messages::DEBUG_ERROR));
						return '';
	        	}
            }
		}
		
        public function admin($args){ return ''; }
        
        public function run($args){
            return true;
        }
        
        public function data($args){
			return '';
        }
		
		public function setup(){
        	
        }
        
        /**
         * Returns an instance of the required UIWidget.
         * 
         * @param The name of the UIWidget.
         * @return AUIWidget
         */
		public function getWidget($name){
			$class = null;
			if(is_file($this->config['widgetFolder'].$name.'.widget.php')){
				require_once($this->config['widgetFolder'].$name.'.widget.php');
				$name = 'UIW_'.$name;
				$class = new $name();
			} 
			return $class;
		}
		
		public function getUploads() {
			$r = array();
			//print_r($_FILES);
			//print_r($_POST);
			if(isset($_POST['action']) && $_POST['action'] == 'upload'){
				switch($_POST['selected_type']){
					case 'html':
						if(isset($_FILES['files']) && isset($_FILES['files']['name']) && $_FILES['files']['name'][0] != ''){
							for($i=0; $i<count($_FILES['files']['name']) ; $i++){
			        			switch($_FILES['files']['error'][$i]){
			        				case 1: //size extends upload_max_filesize directive in php.ini
			        					$this->__(str_replace('{@pp:file}', $_FILES['files']['name'][$i], $this->_('ERROR_MAX_FILE_SIZE')));
			        					break;
			        				case 2: //size extends MAX_FILE_SIZE
			        					$this->__(str_replace('{@pp:file}', $_FILES['files']['name'][$i], $this->_('ERROR_MAX_FILE_SIZE')));
			        					break;
			        				case 3: //The uploaded file was only partially uploaded. 
			        					$this->__(str_replace('{@pp:file}', $_FILES['files']['name'][$i], $this->_('ERROR_UPLOAD_PARTIALLY')));
			        					break;
			        				case 4: // no file uploaded - just debug error
			        					$this->__($this->_('ERROR_NO_FILE_UPLOADED'), Messages::DEBUG_ERROR);
			        					break;
			        				case 6: //Missing a temporary folder
			        					$this->__($this->_('ERROR_MISSING_TEMP_FOLDER'), Messages::DEBUG_ERROR);
			        					break;
			        				case 7: //cant write at disk
			        					$this->__($this->_('ERROR_CANT_WRITE_DISK'), Messages::DEBUG_ERROR);
			        					break;
			        				case 8: //php extension stopped upload
			        					$this->__($this->_('ERROR_EXTENTION_STOPPED_UPLOAD'), Messages::DEBUG_ERROR);
			        					break;
			        				case 0: //ok
			        					$r[] = array('name'=>$_FILES['files']['name'][$i], 
			        								 'tmp_name'=>$_FILES['files']['tmp_name'][$i], 
			        								 'error'=>$_FILES['files']['error'][$i], 
			        								 'size'=>$_FILES['files']['size'][$i],
			        								 'type'=>$_FILES['files']['type'][$i]);
			        					break;
			        			}
							} 
						}
						break;
					case 'flash':
						if(is_dir($this->config['tmpFolder'])){
							$count=0;
							if ($handle = opendir($this->config['tmpFolder'])) {
							    while (false !== ($file = readdir($handle))) {
							        if ($file != '.' && $file != '..' && substr($file, 0, 1) != '.') {
							        	//if(preg_match("/\." . $types . "$/i", $file)){
								        	$size = filesize($this->config['tmpFolder'].$file);
								        	$type = pathinfo($this->config['tmpFolder'].$file);
								        	/*if($size > $_POST['MAX_FILE_SIZE']){			//size extends MAX_FILE_SIZE
			        							$this->__(str_replace('{@pp:file}', $_FILES['files']['name'][$i], $this->_('ERROR_MAX_FILE_SIZE')));
								        	} else {*/
								        		$count++;
								        		$r[] = array('name'=>$file,
								        					'tmp_name'=>$this->config['tmpFolder'].$file,
								        					'error'=>0,
								        					'size'=>$size,
								        					'type'=>$type['extension']);
								        	//}
							        //	}
							        }
							    }
				    			closedir($handle);
							}
							if($count==0) $this->__($this->_('NO_UPLOADS'));
						} 
						break;
					case 'ftp':
						if(is_dir($this->config['ftpFolder'])){
							$count=0;
							if ($handle = opendir($this->config['ftpFolder'])) {
							    while (false !== ($file = readdir($handle))) {
							        if ($file != '.' && $file != '..' && substr($file, 0, 1) != '.') {
							        	//if(preg_match("/\." . $types . "$/i", $file)){
								        	$size = filesize($this->config['ftpFolder'].$file);
								        	$type = pathinfo($this->config['ftpFolder'].$file);
								        	/*if($size > $_POST['MAX_FILE_SIZE']){			//size extends MAX_FILE_SIZE
			        							$this->__(str_replace('{@pp:file}', $_FILES['files']['name'][$i], $this->_('ERROR_MAX_FILE_SIZE')));
								        	} else {*/
								        		$count++;
								        		$r[] = array('name'=>$file,
								        					'tmp_name'=>$this->config['ftpFolder'].$file,
								        					'error'=>0,
								        					'size'=>$size,
								        					'type'=>$type['extension']);
								        	//}
							        //	}
							        }
							    }
				    			closedir($handle);
							}
							if($count==0) $this->__($this->_('NO_UPLOADS'));
						} 
						break;
					default:
						$this->__(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core'))); //Error Message Internal Error
						break;
				}
				
			} 
			return $r;
		}
		
		public function getFTPFiles($types, $ar= false) {
			if(is_dir($this->config['ftpFolder'])){
				$count=0;
				$tpl = new ViewDescriptor($this->config['tpl']['ftp_tmp']);
				if ($handle = opendir($this->config['ftpFolder'])) {
				    while (false !== ($file = readdir($handle))) {
				        if ($file != '.' && $file != '..') {
				        	if(preg_match("/\." . $types . "$/i", $file)){
					        	$sv = new SubViewDescriptor('file');
					        	$sv->addValue('name', $file);
					        	$tpl->addSubView($sv);
					        	unset($sv);
					            $count++;
				        	}
				        }
				    }
	    			closedir($handle);
				}
				if($count == 0) $tpl->showSubView('no_files');
				return $tpl->render();
			} else return 'ERR:1';
		}
	
	}
?>