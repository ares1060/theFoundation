<?php
	/**
	 * Class for handling setting Values
	 * @author Matthias (scrapy1060@gmail.com)
	 * @version 0.1 
	 */
	class SettingValue{
		private $name;
		private $value;
		private $type;
		private $info;
		private $group;
		
		private $options;
		private $hidden;

		function __construct($name, $value, $type, $info='') {
			// strip slashes if needed
			if(($type == SettingFile::TYPE_STRING || $type == SettingFile::TYPE_UNDEFINED) && $value{0} == '"' || ($type == SettingFile::TYPE_SELECT && $value{0} == '"')){
				$value = trim($value, '"');
			}
			if(($type == SettingFile::TYPE_BOOLEAN)) $value = ($value=='true' || $value=='1');

			$this->value = $value;
			$this->name = $name;
			$this->type = $type;
			$this->info = $info;
			$this->options = array();
			$this->hidden = false;
		}
		
		public function setHidden($hidden) { $this->hidden = !(!$hidden); }
		public function setValue($value) { $this->value = $value; }
		
		public function addOptions($options){
			if($this->type == SettingFile::TYPE_SELECT){
				if(is_array($options)) $this->options = array_merge($this->options, $options);
				else $this->options[] = $options; 
			}
		}
		
		public function setGroup($group) { $this->group = $group; }
		
		public function getName() { return $this->name; }
		public function getValue() { return $this->value; }
		public function getType() { return $this->type; }
		public function getInfo() { return $this->info; }
		public function isHidden() { return $this->hidden; }
		public function getOptions() { return $this->options; }
		
		/**
		 * returnes string for use in ini file of this value
		 */
		public function toFile() {
			$string = '';
			
			// header
			if($this->info != '') $string .= '; @info '.$this->info."\n";
			if($this->type != SettingFile::TYPE_UNDEFINED) $string .= '; @type '.$this->type."\n";
			if($this->hidden) $string .= '; @hidden'."\n";
			if($this->options != array()) {
				foreach($this->options as $o){
					$string .= '; @option '.$o."\n";
				}
			}
			
			// var
			if($this->type == SettingFile::TYPE_STRING || $this->type == SettingFile::TYPE_UNDEFINED) $string .= $this->name.' = "'.$this->value.'"'."\n";
			else $string .= $this->name.' = '.$this->value."\n";
			
			$string .= "\n";
			
			return $string;
		}
	}
?>