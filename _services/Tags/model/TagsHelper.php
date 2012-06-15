<?php
	class TagsHelper extends TFCoreFunctions{
		
		private $serviceTags;
		protected $name;
		
		function __construct(){
			parent::__construct();
			$this->serviceTags = array();
			$this->name = 'Tags';
		}
		/* ===========================  RIGHTS  ===========================  */
		public function allowUser($service, $u_id){
			return $this->sp->ref('Rights')->authorizeUser('Tags', 'administer_tags', $u_id, $service);
		}
		
		/* ===========================  GETTER  ===========================  */
		/**
		 * checks if Tag exists with given service and param
		 * @param $name
		 * @param $service
		 * @param $param
		 */
		public function tagExists($name, $service, $param){
			$r = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'tags` t 
											LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'tags_link` tl ON t.t_id = tl.t_id
											WHERE t.name="'.mysql_real_escape_string($name).'" 
											AND tl.service="'.mysql_real_escape_string($service).'"
											AND tl.param ="'.mysql_real_escape_string($param).'"');
			if($r != ''){
				return isset($r['name']);
			} else return false;
		}
		
		/**
		 * returnes Tag by Id
		 * 
		 * @param unknown_type $id
		 */
		public function getTag($id){
			if($id > 0){
				$a = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'tags` WHERE t_id="'.mysql_real_escape_string($id).'"');
				if($a != ''){
					return new TagsTag($a['t_id'], $a['name'], $a['webname']);
				} else return null;
			} else return null;
		}
		
		/**
		 * returnes Tag by Name
		 * 
		 * @param unknown_type $id
		 */
		public function getTagByName($name){
			if($name != ''){
				$a = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'tags` WHERE `name`="'.mysql_real_escape_string($name).'"');
				if($a != ''){
					return new TagsTag($a['t_id'], $a['name'], $a['webname']);
				} else return null;
			} else return null;
		}
		
		/**
		 * returnes Tag by Webname
		 * 
		 * @param unknown_type $id
		 */
		public function getTagByWebname($webname){
			if($webname != ''){
				$a = $this->mysqlRow('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'tags` WHERE `webname`="'.mysql_real_escape_string($webname).'"');
				if($a != ''){
					return new TagsTag($a['t_id'], $a['name'], $a['webname']);
				} else return null;
			} else return null;
		}
		
		/**
		 * returnes all connected Params By given Tag and service
		 * @param TagsTag $tag
		 */
		public function getParamsForTag(TagsTag $tag, $service){
			$a = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'tags_link` WHERE t_id="'.mysql_real_escape_string($tag->getId()).'" AND service="'.mysql_real_escape_string($service).'"');
			if(is_array($a)) {
				$return = array();
				foreach($a as $a_){
					$return[] = $a_['param'];
				}
				return $return;
			} else return array();
		}
		
		/**
		 * returnes Tags By Service
		 * cached
		 * 
		 * @param unknown_type $service
		 * @param unknown_type $param
		 */
		public function getTagsByService($service, $param=''){
			if($service != ''){
				if($this->serviceTags == array() || !isset($this->serviceTags[$service])){
					$param = ($param != '') ? ' AND tl.param="'.mysql_real_escape_string($param).'"' : '';
					
					$a = $this->mysqlArray('SELECT * FROM `'.$GLOBALS['db']['db_prefix'].'tags_link` tl
													LEFT JOIN `'.$GLOBALS['db']['db_prefix'].'tags` t ON tl.t_id = t.t_id 
													WHERE tl.service="'.mysql_real_escape_string($service).'" '.$param);
					if($a != ''){
						$this->serviceTags[$service] = array();
						foreach($a as $tag){
							$this->serviceTags[$service][] = new TagsTag($tag['t_id'], $tag['name'], $tag['webname']);
						}
					} else {
						return array();
						break;
					}
				}
				return $this->serviceTags[$service];
			} else return array();
		}

		/**
		 * Returnes TagCount by Tag id
		 * 
		 * @param unknown_type $tag_id
		 */
		public function getTagCount($tag_id, $service=''){
			if($tag_id > 0){
				$service = ($service == '') ? '' : ' AND service="'.mysql_real_escape_string($service).'" ';
				$a = $this->mysqlRow('SELECT COUNT(*) myCount FROM `'.$GLOBALS['db']['db_prefix'].'tags_link` WHERE t_id="'.mysql_real_escape_string($tag_id).'"'.$service);
				if($a != ''){
					return $a['myCount'];
				} else return -1;
			} else return -1;
		}
		
		/* ===========================  SETTER  ===========================  */
		/**
		 * Creates new Tag in Database
		 * 
		 * @param unknown_type $name
		 */
		private function newTag($name){
			return $this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'tags` (`name`, `webname`) 
												VALUES ("'.mysql_real_escape_string($name).'", "'.mysql_real_escape_string($this->sp->ref('TextFunctions')->string2Web($name)).'")');
		}
		
		/**
		 * Adds Tag To Service and Parameter
		 * 
		 * @param unknown_type $tag_name
		 * @param unknown_type $service
		 * @param unknown_type $param
		 */
		public function addTag($tag_name, $service, $param){
			if($this->checkRight('administer_tags', $service)) {
				$tag = $this->getTagByName($tag_name);
				if($tag == null) $tag = $this->getTag($this->newTag($tag_name));
				
				if($this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'tags_link` 
												(`t_id`, `service`, `param`) VALUES 
												("'.mysql_real_escape_string($tag->getId()).'", 
												 "'.mysql_real_escape_string($service).'", 
												 "'.mysql_real_escape_string($param).'")') !== false) {
					
					//$this->_msg($this->_('_tag add success'), Messages::INFO);
					return true;
				} else {
					//$this->_msg($this->_('_tag add error'), Messages::ERROR);
					return false;
				}
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
			}
		}
		
		/* ===========================  DELETER  ===========================  */
		/**
		 * deletes all Tags from Service
		 * @param unknown_type $service
		 * @param unknown_type $param
		 */
		public function deleteServiceTags($service, $param){
			if($this->checkRight('administer_tags', $service)) {
				$tags = $this->getTagsByService($service, $param);
				
				foreach($tags as $tag) $this->deleteTagFromService($tag, $service, $param);
				
				return true;
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
			}
		}
		
		/**
		 * Deletes Tag From Service 
		 * notice $tag has to be a TagsTag object -> user $delteTagFromServiceById or $delteTagFromServiceByName
		 * 
		 * @param TagsTag $tag
		 * @param unknown_type $service
		 * @param unknown_type $param
		 */
		public function deleteTagFromService(TagsTag $tag, $service, $param){
			if($this->checkRight('administer_tags', $service)) {
				$count = $this->getTagCount($tag->getId());
				$error = !$this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'tags_link` 
													WHERE t_id="'.mysql_real_escape_string($tag->getId()).'"
													AND service="'.mysql_real_escape_string($service).'"
													AND param="'.mysql_real_escape_string($param).'"');	
				
				if(!$error && $count == 1){
					$error = !$error && !$this->mysqlDelete('DELETE FROM `'.$GLOBALS['db']['db_prefix'].'tags` 
													WHERE t_id="'.mysql_real_escape_string($tag->getId()).'"');
				}
				
				if($error){
					//$this->_msg($this->_('_tag delete error'), Messages::ERROR);
					return false;
				} else {
					//$this->_msg($this->_('_tag delete success'), Messages::INFO);
					return false;
				}
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
			}
		}
		
		/**
		 * deletes Tag From Service By Id
		 * 
		 * @param unknown_type $tag_name
		 * @param unknown_type $service
		 * @param unknown_type $param
		 */
		public function deleteTagFromServiceById($tag_id, $service, $param){
			return $this->deleteTagFromService($this->getTag($tag_id), $service, $param);
		}
		
		/**
		 * deletes Tag From Service By Name
		 * 
		 * @param unknown_type $tag_name
		 * @param unknown_type $service
		 * @param unknown_type $param
		 */
		public function deleteTagFromServiceByName($tag_name, $service, $param){
			return $this->deleteTagFromService($this->getTagByName($tag_name), $service, $param);
		}
		
	}
?>