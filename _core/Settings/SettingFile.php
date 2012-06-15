<?php
	/**
	 * Class for handling setting files
	 * @author Matthias (scrapy1060@gmail.com)
	 * @version 0.1 
	 */
	class SettingFile extends TFCoreFunctions{
		private $service;
		private $file;
		private $file_hash;
		private $groups;
		
		const TYPE_UNDEFINED = 'undefined';
		const TYPE_INT = 'int';
		const TYPE_DOUBLE = 'double';
		const TYPE_BOOLEAN = 'bool';
		const TYPE_STRING = 'string';
		const TYPE_SELECT = 'select';
		
		function __construct($file, $service) {
			parent::__construct();
			$this->service = $service;
			$this->file = $file;
        	// TODO: caching via serialize
			$this->groups = array();
			$this->parseSettingFile();
		}	
		
		function __sleep(){
			return array('service', 'file', 'groups');
		}
		
		private function parseSettingFile(){
			if(is_file($this->file)){
				$this->parse_ini($this->file);
			}
		}
		
		public function saveSettingFile(){
			$string = '';
			foreach($this->groups as $g){
				$string .= $g->toFile();
			}
			return ($this->sp->ref('Filehandler')->view(array('file'=>'_services/'.$this->file, 'action'=>'write', 'data'=>$string)) == '');
			
		}
		
		/* --- getter --- */
		/**
		 * returnes group by name
		 * @param unknown_type $name
		 */
		public function getGroup($name) {
			foreach($this->groups as $g){
				if($g->getName() == $name) {
					return $g;
					break;
				}
			}
			return null;
		}
		
		/**
		 * returnes value by group and name
		 * if no group is set group will be searched
		 * @param $name
		 * @param $group
		 */
		public function getValue($name, $group=-1){
			if($group !== -1){
				$g = $this->getGroup($group);
				if($g != null) return $g->getValue($name);
				else return null;
			} else {
				// group will be searched
				foreach($this->groups as $g){
					
					if($g->getValue($name) != null) return $g->getValue($name);
				}
				return null;
			}
		}
		
		/**
		 * returnes all groups
		 */
		public function getGroups() { return $this->groups; }
		
		/**
		 * updates SettingValue Objects from array
		 * @param unknown_type $groups
		 */
		public function updateSettings($groups) {
			if($this->sp->ref('Settings')->isAllowedToEditSettings($this->service)){
				$allowed_hidden = $this->sp->ref('Settings')->isAllowedToEditHiddenSettings($this->service);
				foreach($groups as $group_name=>$group){
					foreach($group as $value_name=>$value){
						$val = $this->getValue($value_name, $group_name);
						if($val->isHidden() && $allowed_hidden) $val->setValue($value);
						else $val->setValue($value);
					}
				}
				$this->debugVar($this->file);
				if($this->saveSettingFile()) $this->_msg($this->_('_settings update success'), Messages::INFO);
				else $this->_msg($this->_('_settings update error'), Messages::ERROR);
				return '';
			} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return '';
           	}
		}
		
		/* --- helper functions --- */
		/**
		 * modified ini parser to recognize extra keywords
		 * and build object structure
		 * @param unknown_type $filepath
		 */
		private function parse_ini ( $filepath ) {
			$ini = file( $filepath );
		    
			if ( count( $ini ) == 0 ) { return false; }
		    
		    //$active_section = array();
		   // $values = array();
		    //$globals = array();
			$tmp_group = new SettingGroup('default');
			
			$active_type = self::TYPE_UNDEFINED;
			$active_options = array();
			$active_info = '';
			$active_hidden = false;
			$active_group_hidden = false;
			$active_group_display_name = '';
			$active_group_desc = '';
			
			$tmp_value = null;
			
		    foreach( $ini as $line ){
		        $line = trim( $line );
		        if($line != ''){
			        // Groups
			        if ( $line{0} == '[' ) {
			        	// add old group to groups array and create new group
			        	if($tmp_group->getCount() > 0) $this->groups[] = $tmp_group;
			            
			        	$tmp_group = new SettingGroup(trim(substr( $line, 1, -1 )), $active_group_desc);
			        	
			        	$tmp_group->setHidden($active_group_hidden);
						$tmp_group->setDisplayName($active_group_display_name);
			        	
			        	// reset group tmp vars
						$active_group_hidden = false;
						$active_group_display_name = '';
						$active_group_desc = '';
			            continue;
			        } else if ( $line == '' || $line{0} == ';' ) { 
			        	$value = trim(substr($line, 1));
			        	if($value != '' && $value{0} == '@'){
			        		$tmp_pos = strpos($value, ' ');
			        		
			        		$keyword = ($tmp_pos === false) ? trim(substr($value, 1)) : trim(substr($value, 1, $tmp_pos ));
			        		//print_r($keyword.'<br />'."\r");
			        		$value = substr($value, $tmp_pos+1);

			        		// switch cases
			        		switch($keyword){
			        			case 'type':
			        				$active_type = $this->getType($value);
			        				break;
			        			case 'option':
			        				$active_options[] = $value;
			        				break;
			        			case 'info':
			        				$active_info .= $value;
			        				break;
			        			case 'hidden':
			        				$active_hidden = true;
			        				break;
			        			case 'group_hidden':
			        				$active_group_hidden = true;
			        				break;
			        			case 'group_display_name':
			        				$active_group_display_name = $value;
			        				break;
			        			case 'group_desc':
			        				$active_group_desc = $value;
			        				break;
			        			default:
			        				// save unrecognized keywords as comment
			        				$tmp_group->addComment(new SettingComment(' @'.$keyword.' '.$value));
			        				break;
			        				
			        		}
			        	} else {
			        		// add normal comment
			        		$tmp_group->addComment(new SettingComment($value));
			        	}
			        	continue; 
			        } else {
				        
				        // get Key-value pair
				        @list( $key, $value ) = explode( '=', $line, 2 );
				        
				        // just if value line
				        if($key != null && $value != null){
					        $key = trim( $key );
					        $value = trim( $value );
					        
					        // create new Settings Object and add to group
					        $tmp_value = new SettingValue($key, $value, $active_type, $active_info);
					        $tmp_value->addOptions($active_options);
					        $tmp_value->setHidden($active_hidden);
					        $tmp_group->addValue($tmp_value);
					        unset($tmp_value);
					        
					        // reset tmp vars
					        $active_options = array();
					        $active_info = '';
							$active_hidden = false;
					        $active_type = self::TYPE_UNDEFINED;
				        } else {
				        	$this->_msg($this->_('_ini file parsing error'), Messages::RUNTIME_ERROR);
				        }
			        }
		        }
		    }
		    // add last group
		    if($tmp_group->getCount() > 0) $this->groups[] = $tmp_group;
		}
		
		/**
		 * returnes type - const for given string of const
		 * @param unknown_type $type
		 */
		private function getType($type){
			if($type == self::TYPE_BOOLEAN || $type == 'boolean') return self::TYPE_BOOLEAN;
			else if($type == self::TYPE_INT) return self::TYPE_INT;
			else if($type == self::TYPE_DOUBLE ) return self::TYPE_DOUBLE;
			else if($type == self::TYPE_SELECT) return self::TYPE_SELECT;
			else if($type == self::TYPE_STRING) return self::TYPE_STRING;
			else return self::TYPE_UNDEFINED;
		}
		
		public function tplSettingAdminCenter() {
			
		}
		
	}
	
	
	class SettingComment{
		private $value;
		
		function __construct($value) { $this->value = $value; }
		
		public function getValue() { return $this->value; }
	}
?>