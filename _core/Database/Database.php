<?php
    class Database extends Service implements IService {
        private $temp;
        private $link;
        private $db;
        private $querycount;
        
        function __construct(){
            $this->name = 'Database';
            $this->querycount = array('success'=>0, 'error'=>0);
            $this->ini_file = $GLOBALS['to_root'].'_core/Database/Database.ini';
            parent::__construct();
            $this->temp = array();
          	//$this->loadConfig($GLOBALS['config']['root'].'_core/Database/Database.config.php');
            //$this->sp->run('Localization', array('load'=>$GLOBALS['config']['root'].'/_localization/core.database.loc.php'));
            $GLOBALS['db']['database_table'] = $this->_setting('db.database_table');
            $GLOBALS['db']['db_prefix'] = $this->_setting('db.db_prefix');
            $this->connect();
        }
        
        public function getSettings() { return $this->settings; }
        
        public function view($args){return ''; }
        public function admin($args){ return ''; }
        
        public function run($args){
            return true;
        }
        
        public function data($args){
            /** query = $query
             *  type = (row, array, insert(bool))
             *  action=(query)
             *  temp = wird temporär gespeichert oder nicht
             */
            $temp1 = (isset($args['temp'])) ? !(!$args['temp']) : true;
            $action = (isset($args['action'])) ? $args['action'] : 'query';
            $query = (isset($args['query'])) ? $args['query'] : '';
            $type = (isset($args['type'])) ? $args['type'] : 'row';
            $r = (isset($args['return'])) ? $args['return'] : false;
            
            if($action == 'query') {
                if($this->queryInCache($query)) return $this->getQueryCache($query);
                else {
                	switch($type){
                		case 'row':
                			return $this->DbGetRow($query);
                			break;
                		case 'bool':
                			return $this->DbGetBool($query, $r);
                			break;
                		case 'array':
                			return $this->DbGetArray($query);
                			break;
                		default:
                			return '';
                	}
                }/*
                if(isset($args['query'])){
                    if(!isset($args['type'])) $args['type'] = 'array';
                    $query = mysql_query($args['query']);
                    $id = mysql_insert_id();
                    
                    $query ? $this->querycount['success']++ : $this->querycount['error']++;
                    if($type == 'row' && $query){   
                        $a = mysql_fetch_assoc($query);
                        if($this->temp) $this->temp[$md5] = $a;
                        return $a;
                    } else if($type == 'bool') {
                    	//$this->debugVar('-'.$id);
                        return ($query) ? (($r) ? $id : true)  : false;
                    } else if($query && $type == 'array'){
                        $a = array();
                        while($row = mysql_fetch_assoc($query)){
                            $a[] = $row;
                        }
                        if($temp1) $this->temp[$md5] = $a;
                        return $a;
                    }
                } else return '';*/
            }
            return '';
        }
        
        /**
         * returnes row of data from the Database usind mysql
         * @param unknown_type $query
         */
        public function DbGetRow($query){
       		$link = mysql_query($query);
       		$id = mysql_insert_id();
       		
       		$link ? $this->querycount['success']++ : $this->querycount['error']++;
       		
       		$a = mysql_fetch_assoc($link);
       		
       		$this->cacheQuery($query, $a);
       		
       		return $a;
        }
        
     	/**
         * returnes if a query ran successfully (eg Insert, Update, Delete)
         * @param unknown_type $query
         */
        public function DbGetArray($query){
       		$link = mysql_query($query);
       		$id = mysql_insert_id();
       		
       		$link ? $this->querycount['success']++ : $this->querycount['error']++;
       		
       		if($link){
	       		$a = array();
	            while($row = mysql_fetch_assoc($link)){
	            	$a[] = $row;
	            }
	       		$this->cacheQuery($query, $a);
	       		
	            return $a;
       		} else return array();
        }
        
   	 	/**
         * returnes the result array of a query
         * @param unknown_type $query
         */
        public function DbGetBool($query, $return){
       		$link = mysql_query($query);
       		$id = mysql_insert_id($this->link);
       		
       		$link ? $this->querycount['success']++ : $this->querycount['error']++;
       		
       		$trace=debug_backtrace();
			$caller=array_shift($trace);
       		
       		if(!$link) $this->_msg('Database Error: '.mysql_error().' (called by: '.$caller.')', Messages::DEBUG_ERROR);
       		
       		$this->cacheQuery($query, $link);
       		
       		return ($link) ? (($return) ? $id : true)  : false;
        }
        
        public function setup(){
        	return true;
        }
        
        public function bool($query) {
            return $this->data(array('query'=>$query, 'type'=>'bool'));
        }

        
        public function exists($query){
            $a = $this->data(array('query'=>$query, 'type'=>'row'));
            if(isset($a) && $a != ""){
                $ak = array_keys($a);
               // print_r($ak);
                if(isset($a[$ak[0]])) return true;
                else return false;
            } else return false;
        }
        
        private function connect(){
        	//$this->link = mysql_connect($GLOBALS['db']['database_host'], $GLOBALS['db']['database_user'], $GLOBALS['db']['database_pwd']) or die (mysql_error());
        	$this->link = mysql_connect($this->_setting('db.database_host'), $this->_setting('db.database_user'), $this->_setting('db.database_pwd')) or die (mysql_error());
        	$this->db = mysql_select_db($this->_setting('db.database_table'));
            mysql_set_charset('utf8');
        }
        
        public function getQueryCount(){
            return $this->querycount;
        }
        
        /**
         * checks if QUery is cached
         * @param $query
         */
     	private function queryInCache($query){
        	$md5 = (isset($query)) ? md5($query) : '';
        	
            return (in_array($md5, $this->temp));
        }
        
        /**
         * returnes cached query
         * @param $query
         */
        private function getQueryCache($query){
        	$md5 = (isset($query)) ? md5($query) : '';
        	
            if(in_array($md5, $this->temp)) return $this->temp[$md5];
            else return '';
        }
        
        /**
         * saves Queryresult to cache
         */
        private function cacheQuery($query, $result){
        	if($this->temp) $this->temp[md5($query)] = 	$result;
        }
        
        /**
         * Function to laziy insert rows into a table.
         * @param string $table The name of the database table to insert into
         * @param array $data An associative array where the keys have the same name as the columns in the database table
         * @return boolean
         */
        function lazyInsert($table, $data){
        	$sql = 'SHOW COLUMNS FROM '.$table.';';//fetch all columns
        	$query = mysql_query($sql);
        	$colstring = '';
        	$valuestring = '';
        	while($column = mysql_fetch_array($query)){
        		//create the field- and value string
        		$colstring .= '`'.$column['Field'].'`,';
        		if(isset($data[$column['Field']])) $valuestring .= '\''.mysql_real_escape_string($data[$column['Field']]).'\', ';
        		else $valuestring .= '\'\', ';
        	}
        	$values = substr($valuestring,0,-2);
        	$cols = substr($colstring,0,-1);
        	return mysql_query('INSERT INTO '.$table.' ('.$cols.') VALUES ('.$values.');');//insert the data
        }
        
        /**
        * Function to lazily update rows in a table.
        * @param string $table The name of the database table to insert into
        * @param string $where A sql WHERE statement excluding the WHERE
        * @param array $data An associative array where the keys have the same name as the columns in the database table.
        * @return boolean
        */
        function lazyUpdate($table, $where, $data){
        	$sql = 'SHOW COLUMNS FROM '.$table.';';//fetch all columns
        	$query = mysql_query($sql);
        	$set = '';
        	while($column = mysql_fetch_array($query)){
        		//create the field- and value string
        		if(isset($data[$column['Field']])){
        			$set .= $column['Field'].'=\''.mysql_real_escape_string($data[$column['Field']]).'\', ';
        		}
        	}
        	$set = substr($set,0,-2);
        	return mysql_query('UPDATE '.$table.' SET '.$set.' WHERE '.$where.';');//insert the data
        }
        
        function reloadConfig() {
        	$this->loadConfigIni($this->ini_file, false);
            require($GLOBALS['config']['root'].'_core/Database/Database.config.php');
            $this->connect();
        }
    }
?>
