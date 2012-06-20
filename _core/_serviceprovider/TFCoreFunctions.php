<?php
	/**
	 * Class providing basic function to Services and Helperclasses
	 * Provides Database Access, Messaging Functions and Rightmanagement
	 * 
	 * will be extended by Service
	 * 
	 * @author matthias
	 */
	class TFCoreFunctions {
        /**
         * @var ServiceProvider
         */
        protected $sp;
        protected $settings;
        
        function __construct(){
        	$this->sp =& $GLOBALS['ServiceProvider'];
        }
		/* ------   Database functions ----- */
        /**
         * returnes Array from the Database
         * @param $query | mysql query
         */
        protected function mysqlArray($query, $return=true){
        	return $this->sp->db->DbGetArray($query);
        }
        
        /**
         * wrapper function of myqlBool
         * @param $query
         * @see mysqlBool
         */
        protected function mysqlInsert($query){
			return $this->mysqlBool($query, true);
        }
        
        /**
         * wrapper function of myqlBool
         * @param $query
         * @see mysqlBool
         */
        protected function mysqlUpdate($query){
			return $this->mysqlBool($query);
        }
        
        /**
         * runs query and returnes true at success
         * @param $query | mysql query
         */
        protected function mysqlBool($query, $return=false){
        	return $this->sp->db->DbGetBool($query, $return);
        }
        
   	 	/**
         * runs query and returnes the returned row
         * @param $query | mysql query
         */
        protected function mysqlRow($query){
        	return $this->sp->db->DbGetRow($query);
        }
        
         /**
         * wrapper function of myqlBool
         * @param $query
         * @see mysqlBool
         */
        protected function mysqlDelete($query){
			return $this->mysqlBool($query);
        }
        
        /**
         * wrapper function of mysqlBool
         * used to create Tables
         * @param $query
         * @see mysqlBool
         */
        protected function mysqlSetup($query){
        	return $this->mysqlBool($query);
        }

        /**
         * Runs serveral Queries seperated by $substr
         * @param $query
         * @param $substr
         */
        protected function mysqlMultipleSetup($query, $seperator=';'){
        	$sql = explode($seperator, $query);
        	$er = array();
			foreach ($sql as $key => $val) {
			    $er[] = !$this->mysqlSetup($val);
			}
			return in_array(false, $er);
        }
        
        protected function mysqlLockTable($table, $action='WRITE'){
        	$this->mysqlBool('LOCK TABLE `' . $table . '` '.$action);
        }
        
        protected function mysqlUnlock() {
        	$this->mysqlBool('UNLOCK TABLES');
        }
	
        
		/* ------   Config functions ----- */
	
        protected function loadConfigIni($file, $cache = true){
        	if(is_file($file)){
        		$this->settings = $this->sp->settings->loadSettingFile($file, $this->name, $cache);
        	}
        }
        
        protected function getSetting($name, $group='default'){
        	if(isset($this->settings)) return $this->settings->getValue($name, $group);
        	else return null;
        }
        
        protected function _setting($name, $group=-1){  
        	$s = $this->getSetting($name, $group); 
        	return ($s != null) ? $s->getValue() : null;
        }
        
        protected function setSettingsCore($settings){
        	$this->settings = $settings;
        }
        
        protected function tplSettings() {
        	return $this->sp->ref('Admincenter')->tplSettings_($this->name, $this->settings);
        }
        
		/* ------   Messaging functions ----- */
        /**
         * lazy right check 
         * @param $action
         * @param $param
         * @param $user_id
         */
        protected function checkRight($action, $param='', $user_id=-1){
        	$user_id = ($user_id == -1 && $this->sp->ref('User')->isLoggedIn()) ? $this->sp->ref('User')->getLoggedInUser()->getId() : $user_id;
        	return $this->sp->ref('Rights')->c($user_id, $this->name, $action, $param);
        }
        
        /**
         * returnes if current session is the foundation-setup session
         */
        protected function isSetup(){
        	return isset($GLOBALS['setup']);
        }
        
        /**
         * creates a Message from var_dump of $var
         * @param $var
         */
        protected function debugVar($var){
        	ob_start(); 
        	var_dump($var);
        	$this->debug(nl2br(ob_get_clean()));
        }
        
        /**
         * creates a Message as Debug type 
         * @param unknown_type $string
         */
        protected function debug($string){
        	$this->_msg($string, Messages::DEBUG);
        }
	
       	/**
       	 * will add an message to the message service
       	 * @param string $str
       	 * @param const $type |Êsee Message
       	 */
        protected function _msg($str, $type=Messages::ERROR){ 
        	$this->sp->msg->run(array('message'=>$str, 'type'=>$type));
       	}
    	/**
       	 * @see _msg
       	 * @param string $str
       	 * @param const $type |Êsee Message
       	 */
        protected function __($str, $type=Messages::ERROR){ 
        	$this->_msg($str, $type);
       	}
       	
		/* --------  localization function ------- */
        /**
         * Returnes string from the Localization Service
         * if no service is declared the own name will be used
         * @param $str | string key in the localization array
         * @param $service 
         */
        protected function _($str, $service=''){
        	if(isset($this->name) && $service == '') 
        		$service = $this->name;
        	 
        	return $this->sp->ref('Localization')->translate($str, $service);
       	}
   	}
?>