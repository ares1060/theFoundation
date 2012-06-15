<?php
	/**
	 * Class for handling setting groups
	 * @author Matthias (scrapy1060@gmail.com)
	 * @version 0.1 
	 */
	class SettingGroup {
		private $contents;
		private $name;
		private $display_name;
		private $hidden;
		private $desc;
		
		private $val_count;
		private $comment_count;

		const COUNT_BOTH = 0;
		const COUNT_VALUES = 1;
		const COUNT_COMMENTS = 2;
		
		function __construct($name, $desc=''){
			$this->name = $name;
			$this->display_name = $name;
			$this->hidden = false;
			$this->desc = $desc;
		} 
		
		/* ---- setter ---- */
		
		public function addValue(SettingValue $val){
			$this->contents[] = $val;
			$this->val_count++;
		}
		
		public function addComment(SettingComment $comment) {
			$this->contents[] = $comment;
			$this->comment_count++;
		} 
		
		public function setDisplayName($name) { if($name != '') $this->display_name = $name; }
		public function setHidden($hidden) { $this->hidden = !(!$hidden); }
		
		/* ---- getter ---- */
		/**
		 * returnes group name
		 */
		public function getName() { return $this->name; }
		public function getDisplayName() { return $this->display_name; }
		public function isHidden() { return $this->hidden; }
		public function getDesc() { return $this->desc; }
		
		/**
		 * returnes Value by name
		 * @param unknown_type $name
		 */
		public function getValue($name) {
			foreach($this->contents as $c){
				if(get_class($c) == 'SettingValue' && $c->getName() == $name) {
					return $c;
					break;
				}
			} 
			return null;
		}
		
		/**
		 * returnes contents as array
		 */
		public function getContents() { return $this->contents; }
		
		/**
		 * returnes Values (no comments) from contents
		 */
		public function getValues() {
			$a = array();
			foreach($this->contents as $c) if(get_class($c) == 'SettingValue') $a[] = $c;
			return $a;
		}
		
		public function getCount($what=self::COUNT_BOTH) { 
			switch($what){
				case self::COUNT_COMMENTS:
					return $this->comment_count;
					break;
				case self::COUNT_VALUES:
					return $this->val_count;
					break;
				default:
					return $this->val_count+$this->comment_count; 
					break;
			}
		}
		
		/**
		 * returnes string for ini file of this group
		 */
		public function toFile() {
			$string = '';
			// render group header
			if($this->name != 'default'){
				if($this->display_name != $this->name) $string .= '; @group_display_name '.$this->display_name."\n";
				if($this->hidden) $string .= '; @group_hidden'."\n";
				$string .= '['.$this->name.']'."\n";
				$string .= "\n";
			}
			
			foreach($this->contents as $c){
				// render value header
				if(get_class($c) == 'SettingValue') $string .= $c->toFile();
				else if(get_class($c) == 'SettingComment') $string .= '; '.$c->getValue()."\n";
				
			}
			return $string;
		}
	}
?>