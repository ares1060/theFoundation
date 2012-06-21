<?php
	
	class Template extends Service implements IService {
        private $templates;
        private $baseReplaces;
        
		private $rawTemplateCache;
		private $rawDynamicCache;
		
		protected $name = 'Template';
		
		/**
		* @var Filehandler
		*/
		private $fh = null;
		
		/**dynamic close tag length*/
		const DCTL = 13;
		
        function __construct(){
        	$this->ini_file = $GLOBALS['to_root'].'_core/Template/Template.ini';
        	
            parent::__construct();
            $this->tmp_render = array();
            $this->tmp_file = array();
            $this->templates = array();
            $this->replace = array();
            $this->find = array();
			
			$this->rawTemplateCache = array();
			$this->rawDynamicCache = array();
		
			$this->phpTemplateCache = array();
			$this->phpDynamicCache = array();
			            
            /*if(!file_exists($GLOBALS['config']['root'].'/'.$this->config['cache_folder'])){
            	mkdir($GLOBALS['config']['root'].'/'.$this->config['cache_folder']);
            }*/
			$this->template = ($this->_setting('tpl.template') == null) ? $this->_setting('tpl.base_template') : $this->_setting('tpl.template');
			
            $this->baseReplaces = array('version'=>'PerPedes V '.ServiceProvider::VERSION, 
                                        'version_short'=>ServiceProvider::VERSION,
                                        'root'=>(isset($GLOBALS['connector_to_root'])) ? $GLOBALS['connector_to_root']: $GLOBALS['to_root'], // connector gets new root if needen because of template rendering from other folder
            							'abs_root'=>$GLOBALS['abs_root'],
            							'working_dir'=>$GLOBALS['working_dir'],
                                        'tpl_root_folder'=>'_template/'.$this->_setting('tpl.base_template').'/'.$GLOBALS['Localization']['language'],
                                        'tpl_folder'=>'_template/'.$this->template.'/'.$GLOBALS['Localization']['language'],
            							'service_folder' => '_services',
                                        'user_id'=> isset($_SESSION['User']['loggedInUser']) ? $_SESSION['User']['loggedInUser']->getId() : '',
                                        'user_group'=> isset($_SESSION['User']['loggedInUser']) ? $_SESSION['User']['loggedInUser']->getGroup()->getName() : '',
            							'user_nick'=> isset($_SESSION['User']['loggedInUser']) ? $_SESSION['User']['loggedInUser']->getNick() : '');
            
            foreach($_GET as $key=>$get){
                if(!is_array($get)) $this->baseReplaces['GET:'.$key] = $get;
            }
            
            $GLOBALS['tpl']['activeTemplate'] = $this->template;
            
            
			//$this->sp->loc->run(array('load'=>$this->config['loc_file']));
        }
        public function getSettings() { return $this->settings; }
        
        /**
         *  args['tpl_url'] ...	template url, the first element is the templates name, every further string seperated with a slash is a sub template
         *  					e.g. news/news_item will load the dynamic "news_item" in the template "news". Note that the url has only two levels
         *  					max since Dynamic names have to be unique within the template.
         */
        public function view($args) {
            if(isset($args['tpl_url']) && $args['tpl_url'] != ''){
            	$parts = explode('/', $args['tpl_url']);
            	if(count($parts) == 1){
            		return $this->renderTemplate($parts[0], $args, array());
            	} else {
            		return $this->renderDynamic($parts[0], $parts[1], $args, array());
            	}
            }
        	return '';
        }
        public function admin($args){
            return '';
        }
        public function run($args){
            return false;
        }
        public function data($args){
            return '';
        }
	    public function setup(){
        	return true;
        }
        

		
		/**
		 *	Renders the specified template and returns the result
		 *	
		 *	@param $tID The name of the template file
		 *	@param $values An array of replace values with the corresponding single value placeholder name as key
		 *  @param $blocks An array of replace values with the corresponding block placeholder name as key
		 *
		 *	@return string
		 */
		function renderTemplate($tID, $values, $blocks){
		    $GLOBALS['extra_css'] = array_unique($GLOBALS['extra_css']);
		    $GLOBALS['extra_js'] = array_unique($GLOBALS['extra_js']);
		    //get the template
		    $this->baseReplaces['extra_css'] = $this->renderCss($GLOBALS['extra_css']);
            $this->baseReplaces['extra_js'] = $this->renderJs($GLOBALS['extra_js']);
			$values = array_merge($values, $this->baseReplaces);
			
			//do the parsing
			if(!isset($this->fb)) $this->fh = $this->sp->ref('Filehandler');;

			if($this->_setting('render.cache_level') == 1){
				if(!isset($this->phpTemplateCache[$tID])) {
					$cacheName = str_replace('/', '_', $tID);
					$cacheName = explode('.', $cacheName);
					$cacheName = $this->fh->getPath($this->_setting('render.cache_folder').'/'.$cacheName[0].'.php');
					if(!file_exists($cacheName)){
						$tpl = $this->getRawTemplate($tID);
						$ca = fopen($cacheName, 'w');
						$tpl = str_replace('\'', '\\\'', $tpl);
						$tpl = $this->replaceValues($tpl, array());
						$tpl = $this->replaceDynamics($tpl, array(), $tID);
						$tpl = $this->parseServiceTags($tpl);
						/*fwrite($ca, '<?php $this->phpTemplateCache[\''.$tID.'\'] = create_function(\'$values, $blocks\', \'return \\\''.addcslashes($tpl, '\'\\').'\\\';\');?>');*/
						fwrite($ca, '<?php $this->phpTemplateCache[\''.$tID.'\'] = \'render_'.str_replace('/', '_', $tID).'\'; function render_'.str_replace('/', '_', $tID).'($values, $blocks){ return \''.$tpl.'\';}?>');
						fclose($ca);
					}
					require_once($cacheName);				
				}
				$tpl = call_user_func($this->phpTemplateCache[$tID], $values, $blocks);	
			} else {
				$tpl = $this->getRawTemplate($tID);
				$tpl = $this->replaceValues($tpl, $values);
				$tpl = $this->replaceDynamics($tpl, $blocks, $tID);
				$tpl = $this->cleanDynamics($tpl);
				$tpl = $this->parseServiceTags($tpl);
				
			}
			
			return $tpl;
		}
		
		/**
		 * 
		 * Helper function for checking file on external link 
		 * Used for RewriteEngine Compatibility
		 * @param $file
		 */
		private function checkIfExtFileExists($file){
			$b = @fopen($file, "r");
			@fclose($b);
			return $b==true;
		}
		
        private function renderCss($array) {
            $return = '';
            foreach($array as $row) {
            	$file = $GLOBALS['abs_root'].'_template/'.$this->template.'/'.$GLOBALS['Localization']['language'].'/css/'.$row;
            	if(!$this->checkIfExtFileExists($file)) $file = $GLOBALS['abs_root'].'_template/'.$this->_setting('tpl.base_template').'/'.$GLOBALS['Localization']['language'].'/css/'.$row;
                $return .= '<link rel="stylesheet" type="text/css" href="'.$file.'" />'."\n";
            }
            return $return;
        }
        
        public function getCssPath($css_file){
        	$file = '_template/'.$this->template.'/'.$GLOBALS['Localization']['language'].'/css/'.$css_file;
        	if(!$this->checkIfExtFileExists($GLOBALS['abs_root'].$file)) $file = '_template/'.$this->_setting('base_template').'/'.$GLOBALS['Localization']['language'].'/css/'.$css_file;
        	return $file;
        }
        
        private function renderJs($array) {
            $return = '';
            foreach($array as $row) {
            	$file = $GLOBALS['abs_root'].'_template/'.$this->template.'/'.$GLOBALS['Localization']['language'].'/js/'.$row;
            	if(!$this->checkIfExtFileExists($file)) $file = $GLOBALS['abs_root'].'_template/'.$this->_setting('base_template').'/'.$GLOBALS['Localization']['language'].'/js/'.$row;
                $return .= '<script type="text/javascript" src="'.$file.'"></script>'."\n";
            }
            return $return;
        }
		
		/**
		 *	Renders the specified dynamic in the specified template and returns the result
		 *	
		 *	@param $tID The name of the template file
		 *	@param $dID The name of the dynamic block in the template
		 *	@param $values An array of replace values with the corresponding single value placeholder name as key
		 *  @param $blocks An array of replace values with the corresponding block placeholder name as key
		 *
		 *	@return string
		 */
		function renderDynamic($tID, $dID, $values, $blocks){
			
			if($this->fh == null) $this->fh = $this->sp->ref('Filehandler');
			
			$values = array_merge($values, $this->baseReplaces);
		
			if($this->_setting('render.cache_level') == 1){
				if(!isset($this->phpDynamicCache[$tID][$dID])){
					$cacheName = str_replace('/', '_', $tID.'/'.$dID);
					$cacheName = explode('.', $cacheName);
					$cacheName = $this->fh->getPath($this->_setting('render.cache_folder').'/'.$cacheName[0].'.php');
					if(!file_exists($cacheName)){
						$ca = $this->fh->openFile($cacheName, 'w');
						$dyn = $this->getRawDynamic($tID, $dID);
						$dyn = str_replace('\'', '\\\'', $dyn);
						$dyn = $this->replaceValues($dyn, array());
						$dyn = $this->replaceDynamics($dyn, array(), $tID);
						$dyn = $this->parseServiceTags($dyn);
						/*fwrite($ca, '<?php $this->phpDynamicCache[\''.$tID.'\'][\''.$dID.'\'] = create_function(\'$values, $blocks\', \'return \\\''.addcslashes($dyn, '\'\\').'\\\';\');?>');*/
						fwrite($ca, '<?php $this->phpDynamicCache[\''.$tID.'\'][\''.$dID.'\'] = \'render_'.str_replace('/', '_', $tID.'_'.$dID).'\'; function render_'.str_replace('/', '_', $tID.'_'.$dID).'($values, $blocks){ return \''.$dyn.'\';}?>');
						fclose($ca);
					}
					require_once($cacheName);
				}
				$dyn = call_user_func($this->phpDynamicCache[$tID][$dID], $values, $blocks);
			} else {
				$dyn = $this->getRawDynamic($tID, $dID);
				$dyn = $this->replaceValues($dyn, $values);
				$dyn = $this->replaceDynamics($dyn, $blocks, $tID);
				$dyn = $this->parseServiceTags($dyn);
			}
			
			return $dyn;
		}
		
		/**
		 *	Returns the unparsed template to the given name
		 *	
		 *	@param $tID The name of the template
		 *
		 *	@return string
		 */
		private function getRawTemplate($tID){
			$tpl = '';
			if(isset($this->rawTemplateCache[$tID])){
				$tpl = $this->rawTemplateCache[$tID];
			} else {
				$file = $GLOBALS['config']['root'].'_template/'.$this->template.'/'.$GLOBALS['Localization']['language'].'/'.$tID.'.html';
				if(!is_file($file)) $file = $GLOBALS['config']['root'].'_template/'.$this->_setting('tpl.base_template').'/'.$GLOBALS['Localization']['language'].'/'.$tID.'.html';
				//echo $file.'<br />';
				//print_r($this->config);
				if(is_file($file)){
					$tpl = $this->loadFile($file);
					$tpl = $this->generateFullyQualifiedNames($tpl, '');
					$this->rawTemplateCache[$tID] = $tpl;
				} else {
					$this->_msg(str_replace(array('{file}'), array($file),$this->_('FILE_NOT_FOUND', 'core')), Messages::DEBUG_ERROR);
					return str_replace(array('{file}'), array($file),$this->_('FILE_NOT_FOUND', 'core'));
				}
			}
			return $tpl;
		}
		
		/**
		 *	Returns the unparsed dynamic to the given name
		 *	
		 *	@param $tID The name of the template
		 *	@param $dID The name of the dynamic
		 *
		 *	@return string
		 */
		private function getRawDynamic($tID, $dID){
			if(isset($this->rawDynamicCache[$tID][$dID])){
				$dyn = $this->rawDynamicCache[$tID][$dID];
			} else {
				//get the template
				$tpl = $this->getRawTemplate($tID);
			
				//find the start and end tag
				$posStart = strpos($tpl, '<pp:dynamic name="'.$dID.'">') + strlen('<pp:dynamic name="'.$dID.'">');
				$nextStart = strpos($tpl, '<pp:dynamic', $posStart);
				$posEnd = strpos($tpl, '</pp:dynamic>', $posStart);
				
				while($nextStart !== false && $nextStart < $posEnd){
					$posEnd = strpos($tpl, '</pp:dynamic>', $posEnd+13);
					$nextStart = strpos($tpl, '<pp:dynamic', $nextStart+20);
				}
				
				if($posStart < $posEnd){
				
					//cut dynamic block from template
					$dyn = substr($tpl, $posStart, $posEnd-$posStart);
					
					//cache unparsed dynamic
					if(!isset($this->rawDynamicCache[$tID])) $this->rawDynamicCache[$tID] = array();
					$this->rawDynamicCache[$tID][$dID] = $dyn;
					
				} else {
					$this->_msg(str_replace(array('{dynamic}', '{template}'), array($dID, $tID), $this->_('DYNAMIC_NOT_FOUND', 'core')), Messages::DEBUG_ERROR);
					return '';
				}
			}
			return $dyn;
		}
		
		/**
		 *	Replaces the dynamic placeholders within the given template.
		 *	e.g. <pp:dynamic name="myDyn"> ... </pp:dynamic> will be replaced with $block['myDyn']
		 *
		 *	@param $tpl The unparsed template
		 *	@param $blocks An array of replace values with the corresponding block placeholder name as key
		 *  @param $tID Template ID for error Message
		 *  
		 *	@return string
		 */
		private function replaceDynamics($tpl, $blocks, $tID=''){
			//find and replace dynamic blocks
			if($this->_setting('render.cache_level') == 1){
				$posStart = strpos($tpl, '<pp:dynamic ');
				while ($posStart !== false) {
					$startEnd = strpos($tpl, '>', $posStart + 15);
					$nameTag = trim(substr($tpl, $posStart + 11, $startEnd-11-$posStart));
					$nameTag = substr($nameTag, 6, -1);
					
					$nextStart = strpos($tpl, '<pp:dynamic ', $posStart + 20);
					$posEnd = strpos($tpl, '</pp:dynamic>', $posStart) + Template::DCTL;
	
					while($nextStart !== false && $nextStart < $posEnd && $posEnd > Template::DCTL){
						$posEnd = strpos($tpl, '</pp:dynamic>', $posEnd) + Template::DCTL;
						$nextStart = strpos($tpl, '<pp:dynamic', $nextStart+20);
					}
					
					$tpl = substr_replace($tpl, '\'.@$blocks[\''.$nameTag.'\'].\'', $posStart, $posEnd - $posStart);
					
					$posStart = strpos($tpl, '<pp:dynamic ', $posStart + 20);
				}
			} else {
				foreach ($blocks as $key => $value) {
					
					$posStart = strpos($tpl, '<pp:dynamic name="'.$key.'">');

					if($posStart !== false){
						$nextStart = strpos($tpl, '<pp:dynamic', $posStart + 20);
						$posEnd = strpos($tpl, '</pp:dynamic>', $posStart) + Template::DCTL;
						
						while($nextStart !== false && $nextStart < $posEnd && $posEnd > Template::DCTL){
							$posEnd = strpos($tpl, '</pp:dynamic>', $posEnd) + Template::DCTL;
							$nextStart = strpos($tpl, '<pp:dynamic', $nextStart+20);
						}
						
						$tpl = substr_replace($tpl, $value, $posStart, $posEnd - $posStart);
					} else {
						$this->_msg(str_replace(array('{@pp:dynamic}', '{@pp:template}'), array($key, $tID), $this->_('DYNAMIC_NOT_FOUND', 'core')), Messages::DEBUG_ERROR);
					}
				}
			}
			
			
			return $tpl;
		}

	    /**
		 *	Replayes the names of the  dynamic placeholders within the given template with their fully qualified names
		 *	e.g. <pp:dynamic name="myDyn"> <pp:dynamic name="mySubDyn"> ... </pp:dynamic> </pp:dynamic> 
		 *	becomes <pp:dynamic name="myDyn"> <pp:dynamic name="myDyn::mySubDyn"> ... </pp:dynamic> </pp:dynamic> 
		 *
		 *	@param $tpl The unparsed template
		 *
		 *	@return string
		 */
		public function generateFullyQualifiedNames($tpl, $path){
			$posStart = strpos($tpl, '<pp:dynamic ');
			
			while ($posStart !== false) {
				$startEnd = strpos($tpl, '>', $posStart + 15);
				$nameTag = trim(substr($tpl, $posStart + 11, $startEnd-11-$posStart));
				$nameTag = substr($nameTag, 6, -1);
				
				$nextStart = strpos($tpl, '<pp:dynamic ', $posStart + 20);
				$posEnd = strpos($tpl, '</pp:dynamic>', $posStart) + Template::DCTL;

				while($nextStart !== false && $nextStart < $posEnd && $posEnd > Template::DCTL){
					$posEnd = strpos($tpl, '</pp:dynamic>', $posEnd) + Template::DCTL;
					$nextStart = strpos($tpl, '<pp:dynamic', $nextStart+20);
				}
				
				$npath = (($path != '')?$path.'_':'').$nameTag;
				$tpl = substr_replace($tpl, '<pp:dynamic name="'.$npath.'">'.$this->generateFullyQualifiedNames(substr($tpl, $startEnd+1, $posEnd - $startEnd - Template::DCTL - 1), $npath).'</pp:dynamic>', $posStart, $posEnd - $posStart);
				
				$posStart = strpos($tpl, '<pp:dynamic ', $posEnd);
			}
			
			return $tpl;
		}
		
    	/**
		 *	Clears the dynamic placeholders within the given template.
		 *
		 *	@param $tpl The unparsed template
		 *
		 *	@return string
		 */
		private function cleanDynamics($tpl){
			//find and replace dynamic blocks
			$posStart = strpos($tpl, '<pp:dynamic');
			while ($posStart !== false) {
				$nextStart = strpos($tpl, '<pp:dynamic', $posStart + 20);
				$posEnd = strpos($tpl, '</pp:dynamic>', $posStart) + Template::DCTL;

				while($nextStart !== false && $nextStart < $posEnd && $posEnd > Template::DCTL){
					$posEnd = strpos($tpl, '</pp:dynamic>', $posEnd) + Template::DCTL;
					$nextStart = strpos($tpl, '<pp:dynamic', $nextStart+20);
				}
				
				if($posEnd > Template::DCTL) $tpl = substr_replace($tpl, '', $posStart, $posEnd - $posStart);
				else break;
				$posStart = strpos($tpl, '<pp:dynamic');
			}
			
			return $tpl;
		}
		
		/**
		 *	Replaces the single value placeholders within the given template.
		 *	e.g. {@pp:myValue} will be replaced with $value['myValue']
		 *
		 *	@param $tpl The unparsed template
		 *	@param $values An array of replace values with the corresponding single value placeholder name as key
		 *
		 *	@return string
		 */
		private function replaceValues($tpl, $values){			
			//find first tag
			$firstPos = strpos($tpl, '{@');
			if($firstPos !== false){
				//if there is a tag go on with replacing
				$front = substr($tpl, 0, $firstPos); 
				$tpl = substr($tpl, $firstPos+2); 			
				
				//split the template into chunks
				$tplChunks = explode('{@', $tpl);
				
				//split all the chunks again
				foreach($tplChunks as &$chunk){
					$parts = explode('}', $chunk, 2);
					$name = explode(':', $parts[0], 2);
					//check if really single value
					if(preg_match('/[^a-zA-Z0-9_]+/', $name[1]) < 1){
						if($this->_setting('render.cache_level') == 1){
							if($name[0] == 'pp') $chunk = '\'.@$values[\''.@$name[1].'\'].\''.@$parts[1];
							else if($name[0] == 'get' || $name[0] == 'GET') $chunk = '\'.@$_GET[\''.@$name[1].'\'].\''.@$parts[1];
							else if($name[0] == 'post' || $name[0] == 'POST') $chunk = '\'.@$_POST[\''.@$name[1].'\'].\''.@$parts[1];
							else if($name[0] == 'server' || $name[0] == 'SERVER') $chunk = '\'.@$_SERVER[\''.@$name[1].'\'].\''.@$parts[1];
							else if($name[0] == 'cookie' || $name[0] == 'COOKIE') $chunk = '\'.@$_COOKIE[\''.@$name[1].'\'].\''.@$parts[1];
							else if($name[0] == 'globals' || $name[0] == 'GLOBALS') $chunk = '\'.@$GLOBALS[\''.@$name[1].'\'].\''.@$parts[1];
						} else {
							if($name[0] == 'pp') $chunk = @$values[$name[1]].@$parts[1];
							else if($name[0] == 'get' || $name[0] == 'GET') $chunk = @$_GET[$name[1]].@$parts[1];
							else if($name[0] == 'post' || $name[0] == 'POST') $chunk = @$_POST[$name[1]].@$parts[1];
							else if($name[0] == 'server' || $name[0] == 'SERVER') $chunk = @$_SERVER[$name[1]].@$parts[1];
							else if($name[0] == 'cookie' || $name[0] == 'COOKIE') $chunk = @$_COOKIE[$name[1]].@$parts[1];
							else if($name[0] == 'globals' || $name[0] == 'GLOBALS') $chunk = @$GLOBALS[$name[1]].@$parts[1];
						}
					} else {
						$chunk = '{@pp:'.$chunk;
					}
				}
				unset($chunk);
				
				return $front.implode($tplChunks);
			} else {
				return $tpl;
			}
		}
		
		/**
		 *	Parses all service tags in a template and replaces them with the results.
		 *	A service tag would look like the following
		 *	{pp:myService(arg1:value, arg2:value2, arg3:value3)}
		 *	
		 *	@param $tpl the unparsed template
		 *
		 *	@return string
		 */
		private function parseServiceTags($tpl){
			//split the template into chunks
			$firstPos = strpos($tpl,'{pp:');
			$tplChunks = explode('{pp:', $tpl);

			if($this->_setting('render.service_tag_mode') == 0){
				//classic servica tag mode
				//split all the chunks again
				foreach($tplChunks as &$chunk){
					if($firstPos === 0){
						$parts = explode(')}', $chunk, 2);
						if(count($parts) == 2){
							$pos = strpos($parts[0], '(');
							//get the arguments
							$argChunks = explode(',', substr($parts[0],$pos+1));
							//parse the arguments
							$args = array();
							foreach($argChunks as &$arg){
								$argPair = explode(':', $arg, 2);
								if(count($argPair) == 2){
									$args[trim($argPair[0])] = trim($argPair[1]);
								}
							}
							unset($arg);
							//get the service result 
							if($this->_setting('render.cache_level') == 1) $result = '\'.$GLOBALS[\'ServiceProvider\']->view(\''.substr($parts[0], 0, $pos).'\', json_decode(\''.json_encode($args).'\', true)).\'';
							else $result = $this->sp->view(substr($parts[0], 0, $pos), $args);
							$chunk = $result.$parts[1];
						}
					} else {
						$firstPos = 0;
					}
				}
				unset($chunk);
			} elseif($this->_setting('render.service_tag_mode') == 1){
				//json service tag mode
				//split all the chunks again
				foreach($tplChunks as &$chunk){
					if($firstPos === 0){
						$parts = explode(')}', $chunk, 2);
						if(count($parts) == 2){
							$pos = strpos($parts[0], '(');
							//get the arguments
							print_r(substr($parts[0],$pos+1));
							$jsonObj = substr($parts[0],$pos+1);
							if(substr($jsonObj, 0, 1) != '{') $jsonObj = '{'.$jsonObj;
							if(substr($jsonObj, -1) != '}') $jsonObj = $jsonObj.'}';
							
							//get the service result
							if($this->_setting('render.cache_level') == 1) $result = '\'.$GLOBALS[\'ServiceProvider\']->view(\''.substr($parts[0], 0, $pos).'\', json_decode(\''.$jsonObj.'\', true)).\'';
							else $result = $this->sp->view(substr($parts[0], 0, $pos), json_decode($jsonObj, true));
							$chunk = $result.$parts[1];
						}
					} else {
						$firstPos = 0;
					}
				}
				unset($chunk);
			} elseif($this->_setting('render.service_tag_mode') == 2){
				//classic service tag mode
				//split all the chunks again
				foreach($tplChunks as &$chunk){
					if($firstPos === 0){
						$parts = explode(')}', $chunk, 2);
						if(count($parts) == 2){
							$pos = strpos($parts[0], '(');
							$sParamString = substr($parts[0],$pos+1);

							if(substr($sParamString, 0, 1) == '"'){
								// json
								$jsonObj = $sParamString;
								if(substr($jsonObj, 0, 1) != '{') $jsonObj = '{'.$jsonObj;
								if(substr($jsonObj, -1) != '}') $jsonObj = $jsonObj.'}';
								
								//get the service result
								if($this->_setting('render.cache_level') == 1) $result = '\'.$GLOBALS[\'ServiceProvider\']->view(\''.substr($parts[0], 0, $pos).'\', json_decode(\''.$jsonObj.'\', true)).\'';
								else $result = $this->sp->view(substr($parts[0], 0, $pos), json_decode($jsonObj, true));
								$chunk = $result.$parts[1];
							} else {
								// normal params
								//get the arguments
								$argChunks = explode(',', $sParamString);
								//parse the arguments
								$args = array();
								foreach($argChunks as &$arg){
									$argPair = explode(':', $arg, 2);
									if(count($argPair) == 2){
										$args[trim($argPair[0])] = trim($argPair[1]);
									}
								}
								unset($arg);
								//get the service result 
								if($this->_setting('.rendercache_level') == 1) $result = '\'.$GLOBALS[\'ServiceProvider\']->view(\''.substr($parts[0], 0, $pos).'\', json_decode(\''.json_encode($args).'\', true)).\'';
								else $result = $this->sp->view(substr($parts[0], 0, $pos), $args);
								$chunk = $result.$parts[1];
							}
						}
					} else {
						$firstPos = 0;
					}
				}
				unset($chunk);
			}
			
			return implode($tplChunks);
		}
		
        private function addTemplate($id){
            if(!in_array($id, array_keys($this->templates))){
                $this->templates[$id] = array();
                return true;
            } else return false;
        }
        
        /**
         * 
         * Sets other Template than the default one specified at config.Template.php.
         *  
         * @param $tpl_name
         */
        public function setTemplate($tpl_name){
        	if($this->isAllowedToChangeTemplate()){
        		$this->template = $tpl_name;
        		$this->baseReplaces['tpl_folder'] = '_template/'.$this->template.'/'.$GLOBALS['Localization']['language'];
        		$GLOBALS['tpl']['activeTemplate'] = $tpl_name;
        	}
        }
        
        /**
         * 
         * Returnes if you can change the Template
         */
        public function isAllowedToChangeTemplate() {
        	return ($this->_setting('tpl.ajax_template_change') == 2 || ($this->_setting('tpl.ajax_template_change') && isset($_SESSION['User'])));	
        }
    }
?>