<?php
	/**
	 * A service for indexing files
	 */
	class Fileindex extends Service implements IService {
	
		function __construct(){
            parent::__construct();
			$this->loadConfig($GLOBALS['config']['root'].'_services/Fileindex/Fileindex.config.php');
        }
        
        public function view($args){ return ''; }
		
        public function admin($args){ return ''; }
        
        public function run($args){
            return true;
        }
        
        public function data($args){
			if($args['action'] == 'insert'){
				if(isset($args['path'])){
					return $this->insertFile($args['path']);
				}
			} else {
				if(isset($args['id'])){
					return $this->idToPath($args['id']);
				} else if(isset($args['path'])){
					return $this->pathToId($args['path']);
				} else if(isset($args['hash'])){
					return $this->hashToPath($args['hash']);
				}
			}
			return '';
        }
        
		public function setup(){
        	return true;
        }
		
		public function idToPath($id){
		
			$data = $this->sp->db->data(array('query' => 'SELECT path FROM '.$GLOBALS['db']['db_prefix'].'fileindex WHERE id =\''.$id.'\';', 'type' => 'row'));
			return $data['path'];
		}
		
		public function pathToId($path){
			$data = $this->sp->db->data(array('query' => 'SELECT id FROM '.$GLOBALS['db']['db_prefix'].'fileindex WHERE path =\''.$path.'\';', 'type' => 'row'));
			return $data['id'];
		}
		
		public function hashToPath($hash){
			$data = $this->sp->db->data(array('query' => 'SELECT path FROM '.$GLOBALS['db']['db_prefix'].'fileindex WHERE hash =\''.$hash.'\';', 'type' => 'row'));
			return $data['path'];
		}
		
		public function insertFile($path){
			$id = $this->pathToId($path);
			if(!$id){
				$this->sp->db->bool('INSERT INTO '.$GLOBALS['db']['db_prefix'].'fileindex (`id`, `path`, `hash`) VALUES (\'\', \''.$path.'\', \''.md5($path).'\');');
				return $this->pathToId($path);
			} else {
				return $id;
			}
		}
	
	}
?>