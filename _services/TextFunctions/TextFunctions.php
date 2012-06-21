<?php
	require_once 'classes/TextFunctionsMailChecker.php';
	require_once 'classes/TextFunctionsPasswordStrength.php';
	/**
     * Description
     * @author author
     * @version: version
     * @name: name
     * 
     * @requires: Services required
     */
    class TextFunctions extends Service implements IService {
        /**
         * protected $name;
         * protected $sp;
         * protected $config;
         */
         
    	const ONLINE = 1;
    	const OFFLINE = 0;
    	
    	private $smilies;
    	
    	private $password_strength;
    	
        function __construct(){
        	$this->name = 'TextFunctions';
        	$this->config_file = $GLOBALS['config']['root'].'_services/TextFunctions/config.TextFunctions.php';

        	parent::__construct();
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::view()
         */
        public function view($args) {
            return '';
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::admin()
         */
        public function admin($args){
            return '';
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::run()
         */
        public function run($args){
            return false;
        }
        /**
         * 
         * Wrapper functions for functions mentioned above.
         *  @param $args['param_name_1'] type_of_param_name_1 | possibilities of param_name_1 (posibility_1, posibility_2)
         *  @param $args['param_name_2'] type_of_param_name_2 | description of param_name_2
         * @see _core/IService::data()
         */
        public function data($args){
        	$action = isset($args['action']) ? $args['action'] : '';
        	$pwd = isset($args['pwd']) ? $args['pwd'] : '';
        	
        	switch($action){
        		case 'getPasswordStrength':
        			return $this->getPasswordStrengthInfo($pwd);
        			break;
        		case 'generatePassword':
        			return $this->generatePassword(14, 4, 3, 1);
        			break;
        	}
            return '';
        }
        
    	public function setup(){
        	
        }
        
        public function hashString($string, $salt, $type){
        	switch($type){
        		case 'whirlpool':
        			return hash('whirlpool', $salt.$string).'#'.$salt;
        			break;
        		case 'sha1':
        			return base64_encode(sha1($salt.$this->generatePassword(51, 13, 7, 7))).'#'.$salt;
        			break;
        	}
        }
        
        public function string2Web($string){
        	$string = str_replace(
        		array(' ', '&auml;', '&Auml;', 
        			'&ouml;', '&Ouml;', '&szlig;', 
        			'&uuml;', '&Uuml;'), 
        		array('_', 'ae', 'Ae', 
        			'oe', 'Oe', 'ss', 
        			'ue', 'Ue'),
        		
        		strtolower($string)
        	);
        	
        	return str_replace(
        		array(' ', '&auml;', '&Auml;', 
        			'&ouml;', '&Ouml;', '&szlig;', 
        			'&uuml;', '&Uuml;'), 
        		array('_', 'ae', 'Ae', 
        			'oe', 'Oe', 'ss', 
        			'ue', 'Ue'),
        		
        		strtolower($this->renderUmlaute($string))
        	);
        }
        
        /**
         * 
         * crops Text at specified length and adds a ...
         * @param string $text
         * @param int $length
         */
        public function cropText($text, $length){
        	if(strlen($text) <= $length || $length < 3) return $text;
        	else {
        		return substr($text, 0, $length-3).'...';
        	}
        }
        
        /**
         * 
         * renders BBCode in text
         * @param string $text
         */
        public function renderBBCode($text) {
        	// breaks
        	$text = nl2br($text);
        	$text = str_replace('\r\n', '<br />', $text);
//        	$text = preg_replace("/\n/Usi","<br />",$text);
			$text = preg_replace("/\[br\]/Usi","<br />",$text);
			$text = preg_replace("/\[hr\]/Usi","<hr />",$text);
			
			//text styles
			$text = preg_replace("/\[b\](.*)\[\/b\]/Usi","<strong>\\1</strong>",$text);
			$text = preg_replace("/\[i\](.*)\[\/i\]/Usi","<i>\\1</i>",$text);
			$text = preg_replace("/\[u\](.*)\[\/u\]/Usi","<u>\\1</u>",$text);
			
			$text = preg_replace("/\[p\](.*)\[\/p\]/Usi","<p>\\1</p>",$text);
			$text = preg_replace("/\[h1\](.*)\[\/h1\]/Usi","<h1>\\1</h1>",$text);
			$text = preg_replace("/\[h2\](.*)\[\/h2\]/Usi","<h2>\\1</h2>",$text);
			$text = preg_replace("/\[h3\](.*)\[\/h3\]/Usi","<h3>\\1</h3>",$text);
			$text = preg_replace("/\[h4\](.*)\[\/h4\]/Usi","<h4>\\1</h4>",$text);
			$text = preg_replace("/\[h5\](.*)\[\/h5\]/Usi","<h5>\\1</h5>",$text);
			$text = preg_replace("/\[h6\](.*)\[\/h6\]/Usi","<h6>\\1</h6>",$text);

			$text = preg_replace("/\[sup\](.*)\[\/sup\]/Usi","<sup>\\1</sup>",$text);
			$text = preg_replace("/\[sub\](.*)\[\/sub\]/Usi","<sub>\\1</sub>",$text);
			
			//text-formats
			$text = preg_replace("/\[left\](.*)\[\/left\]/Usi","<div style=\"text-align:left;\">\\1</div>",$text);
			$text = preg_replace("/\[right\](.*)\[\/right\]/Usi","<div style=\"text-align:right;\">\\1</div>",$text);
			$text = preg_replace("/\[center\](.*)\[\/center\]/Usi","<div style=\"text-align:center;\">\\1</div>",$text);
			$text = preg_replace("/\[block\](.*)\[\/block\]/Usi","<div style=\"text-align:justify;\">\\1</div>",$text);
			
			//lists
			$text = preg_replace('#\[list\](.*)\[/list\]#isU', "<ul>$1</ul>", $text);
  			$text = preg_replace('#\[list=(1|a)\](.*)\[/list\]#isU', "<ol type=\"$1\">$2</ol>", $text);
			$text = preg_replace("/\[\*\](.*)\[\/\*\]/Usi","<li>\\1</li>",$text);
			
  			  
			//images
			$text = preg_replace("/\[img=([^\[]+) width=([^\[]+)\]/Usi",'<img src="\\1" width="\\2"/>',$text);
			$text = preg_replace("/\[img=([^\[]+)\]/Usi",'<img src="\\1"/>',$text);
			
			//links
			$text = preg_replace("/\[mail\]([^\[]+)\[\/mail\]/Usi","<a href=\"mailto:\\1\">\\1</a>",$text);
			$text = preg_replace("/\[url\]http:\/\/([^\[]+)\[\/url\]/Usi","<a href=\"http://\\1\" target=\"_blank\">\\1</a>",$text);
			$text = preg_replace("/\[url=http:\/\/([^\"]+)]([^\[]+)\[\/url\]/Usi","<a href=\"http://\\1\">\\2</a>",$text);
			$text = preg_replace("/\[url\]([^\[]+)\[\/url\]/Usi","<a href=\"".$GLOBALS['to_root']."\\1\" target=\"_blank\">\\1</a>",$text);
			$text = preg_replace("/\[url=([^\"]+)]([^\[]+)\[\/url\]/Usi","<a href=\"".$GLOBALS['to_root']."\\1\">\\2</a>",$text);
			
			// quicktime movie
			$text = preg_replace("/\[QTVideo url=([^\[]+)\]/Usi", '<OBJECT CLASSID="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
								CODEBASE="http://www.apple.com/qtactivex/qtplugin.cab"
								HEIGHT="376"
								WIDTH="600"
								autoplay="true"
								controller="true">
	 							<PARAM NAME="src" VALUE="\\1">
	 							<EMBED
									SRC="\\1"
									HEIGHT="376" WIDTH="600"
									autoplay="true"
									controller="true"
									TYPE="video/quicktime"
									PLUGINSPAGE="http://www.apple.com/at/quicktime/download/"
								/> 
						</OBJECT>', $text);
			
			//youtube video
			$text = preg_replace("/\[yt url=([^\"]+)\]/Usi", "\[youtube url=\\1\]", $text);
			$text = preg_replace("/\[yt src=([^\"]+)\]/Usi", "\[youtube url=\\1\]", $text);
			$text = preg_replace("/\[youtube url=([^\"]+)\]/Usi", "\[youtube url=\\1\]", $text);
			$text = preg_replace("/\[youtube src=([^\"]+)\]/Usi", '<object style="height: 390px; width: 640px">
							<param name="movie" value="\\1?version=3">
							<param name="allowFullScreen" value="true">
							<param name="allowScriptAccess" value="always">
							<embed src="\\1?version=3" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" width="640" height="390">
						</object>', $text);
			return $this->renderSmilies($text);
        }
        
        /**
         * loads and renders bbcode from database
         * @param unknown_type $text
         */
        private function renderBBCodeDB($text){
        	if(!isset($this->db_bbcode)){
        		$this->db_bbcode = array();
        		$smilies = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'textfunctions_bbcode WHERE status="'.self::ONLINE.'";');
        		if(is_array($smilies)){
        			foreach($smilies as $s){
        				$this->db_bbcode[] = array('preg'=>$s['preg'], 'replace'=>$s['replace']);
        				$text = str_replace($s['preg'], $s['replace'], $text);
        			}
        		}
        	} else {
        		foreach($this->db_bbcode as $s){
        			$text = str_replace($s['preg'], $s['replace'], $text);
        		}
        	}
        	return $text;
        }
        
        /**
         * adds BBCode to database table
         * @param $preg
         * @param $replace
         * @param $service
         * @param $status
         */
        public function addBBCode($preg, $replace, $service, $status=-1){
        	if($this->checkRight('administerBBCode') || $this->isSetup()){
        		if($status == -1) $status = self::ONLINE;
        		return $this->mysqlInsert('INSERT INTO `'.$GLOBALS['db']['db_prefix'].'textfunctions_bbcode` (`preg`, `replace`, `service`, `status`) VALUES 
        									("'.mysql_real_escape_string($preg).'",
        									"'.mysql_real_escape_string($replace).'",
        									"'.mysql_real_escape_string($service).'",
        									"'.mysql_real_escape_string($staus).'");');
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
        public function deleteBBCodeForService($service){
        	if($this->checkRight('administerBBCode')){
        		
        	} else {
        		$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
        	}
        }
        
        /**
         * 
         * renders Umlaute
         * @param $text
         */
        public function renderUmlaute($text){
        	//$this->debugVar(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES, 'UTF-8'));
        	return nl2br(htmlentities($this->stripBr($text), ENT_QUOTES , 'UTF-8'));
        	//return htmlentities($text, ENT_QUOTES , mb_detect_encoding($text));
        }
    	/**
    	 * removes <br /> tags from $text
    	 * @param unknown_type $text
    	 */
        public function stripBr($text) {
        	return str_replace(array('<br />', '<br>', '<br >'), array('', '', ''), $text);
        }
        /**
         * 
         * renders Smilies in a text
         * @param $text
         */
        public function renderSmilies($text){
        	if(!isset($this->smilies)){
        		$this->smilies = array();
        		$smilies = $this->mysqlArray('SELECT * FROM '.$GLOBALS['db']['db_prefix'].'textfunctions_smilies WHERE status="'.self::ONLINE.'";');
        		if(is_array($smilies)){
        			foreach($smilies as $s){
        				$this->smilies[] = array('code'=>$s['code'], 'img'=>$s['img']);
        				$text = str_replace($s['code'], '<img src="'.$this->config['smilies_path'].$s['img'].'" class="smilie" />', $text);
        			}
        		}
        	} else {
        		foreach($this->smilies as $s){
        			$text = str_replace($s['code'], '<img src="'.$this->config['smilies_path'].$s['img'].'" class="smilie" />', $text);
        		}
        	}
        	return $text;
        }
        
        
        /**
         * returnes Ip address
         */
        public function getIp() {
        	$ip = null;
			if (getenv('HTTP_CLIENT_IP'))
				$ip = getenv('HTTP_CLIENT_IP');
			else if(getenv('HTTP_X_FORWARDED_FOR'))
				$ip = getenv('HTTP_X_FORWARDED_FOR');
			else if(getenv('REMOTE_ADDR'))
				$ip = getenv('REMOTE_ADDR');
			else
				$ip = 'UNKNOWN';
			return $ip;
        }
        
        
        /**
         * 
         * returnes a string how long ago the date is from now
         * eg: 5 min ago
         *     10 days ago
         * @param int(Unix Timestamp) $date
         */
        public function getDateAgo($date){
        	$now = time();
        	$diff = $now - $date;
        	$return = '';
        	
        	if($this->config['show_date_long_ago'] && $diff >= 31536000){
        		$year = date('Y', $date);
        		$month = date('m', $date);
        		$day = date('d', $date);
        		$return .= str_replace(array('{year}', '{month}', '{day}'), array($year, $month, $day), $this->_('DATE')) ;
        	} else {
	        	if($diff < 60){
	        		$return .= ($diff==1) ? str_replace('{date}', $diff, $this->_('DATE_SECOND')) : str_replace('{date}', $diff, $this->_('DATE_SECONDS'));
	        	} else if($diff < 3600){
	        		$diff = round($diff / 60);
	        		$return .= ($diff==1) ? str_replace('{date}', $diff, $this->_('DATE_MIN')) : str_replace('{date}', $diff, $this->_('DATE_MINS'));
	        	} else if($diff < 86400){//216000){
	        		$diff = round($diff / 3600);
	        		$return .= ($diff==1) ? str_replace('{date}', $diff, $this->_('DATE_HOUR')) : str_replace('{date}', $diff, $this->_('DATE_HOURS'));
	        	} else if($diff < 2628000){//5184000){
	        		$diff = round($diff / 86400);//216000);
	        		
	        		if($diff > 7){
	        			$diff = round($diff/7);
	        			$return .= ($diff==1) ? str_replace('{date}', $diff, $this->_('DATE_WEEK')) : str_replace('{date}', $diff, $this->_('DATE_WEEKS'));
	        		} else {
	        			$return .= ($diff==1) ? str_replace('{date}', $diff, $this->_('DATE_DAY')) : str_replace('{date}', $diff, $this->_('DATE_DAYS'));
	        		}
	        		
	        	} else if($diff < 31536000) {
	        		$diff = round($diff / 2628000);
	        		$return .= ($diff==1) ? str_replace('{date}', $diff, $this->_('DATE_MONTH')) : str_replace('{date}', $diff, $this->_('DATE_MONTHS'));
	        	} else {//if($diff < 155520000) {
	        		$diff = round($diff / 31536000);
	        		$return .= ($diff==1) ? str_replace('{date}', $diff, $this->_('DATE_YEAR')) : str_replace('{date}', $diff, $this->_('DATE_YEARS'));
	        	}
        	}
        	
        	return $return;
        }
        
        /**
		 * Validate an email address.
		 * Provide email address (raw input)
		 * Returns true if the email address has the email 
		 * address format and the domain exists.
		 * @see http://www.linuxjournal.com/article/9585?page=0,3 (25.02.2012)
		 */
		function isEmail($email) {
			$isValid = true;
			$atIndex = strrpos($email, "@");
			if (is_bool($atIndex) && !$atIndex){
		   		$isValid = false;
			} else {
				$domain = substr($email, $atIndex+1);
				$local = substr($email, 0, $atIndex);
				$localLen = strlen($local);
				$domainLen = strlen($domain);
				if ($localLen < 1 || $localLen > 64) {
			         
			      	// local part length exceeded
			      	$isValid = false;
				} else if ($domainLen < 1 || $domainLen > 255) {
			         
			      	// domain part length exceeded
			      	$isValid = false;
				} else if ($local[0] == '.' || $local[$localLen-1] == '.') {
			         
			      	// local part starts or ends with '.'
			      	$isValid = false;
				}  else if (preg_match('/\\.\\./', $local)) {
			      	
			      	// local part has two consecutive dots
			      	$isValid = false;
				}  else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))  {
	
			      	// character not valid in domain part
				$isValid = false;
				} else if (preg_match('/\\.\\./', $domain)) {
			         
			      	// domain part has two consecutive dots
			      	$isValid = false;
				} else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
			         
			      	// character not valid in local part unless 
			     	// local part is quoted
			      	if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
						$isValid = false;
					}
				}
			      
				if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
			         
					// domain not found in DNS
					$isValid = false;
				}
			}
			return $isValid;
		}
		
	    /**
	 	 * Return strength value from 1 - 10 of $password
	 	 * @see http://www.phpro.org/examples/Password-Strength-Tester.html (24.02.2012)
	 	 * @param $password
	 	 * @return int 
	 	 */
		
		function OLDgetPasswordStrength($password) {
			if ( strlen( $password ) == 0 ) {
		        return 0;
		    }
		
		    $strength = 0;
		
		    /*** get the length of the password ***/
		    $length = strlen($password);
		    
		    /** ------------------------------------ length -----------*/
			/*** check string length is < 7 chars ***/
		    if($length < 8) {
		        $strength -= 20; // was 1
		    }
		    
		    /*** check string length is 8 - 15 chars ***/
		    if($length >= 8 && $length <= 15) {
		        $strength += 20; // was 1
		    }
		
		    /*** check if lenth is 16 - 35 chars ***/
		    if($length >= 16 && $length <=35) {
		        $strength += 30; // was 2
		    }
		
		    /*** check if length greater than 35 chars ***/
		    if($length > 35) {
		        $strength += 40; // was 3
		    }
		    
		    /** ------------------------------------ numbers/special -----------*/
		    /*** get the numbers in the password ***/
		    preg_match_all('/[0-9]/', $password, $numbers);
		    $strength += count($numbers[0])*4;
		    if(count($numbers[0] > 0)) $strength += 20;
		
		    /*** check for special chars ***/
		    
		    preg_match_all('/[\|\!\@\#\$\%\&\*\/\=\?\-\_\+\^\(\)\:\;\,\.]/', $password, $specialchars);

		    $strength += sizeof($specialchars[0])*4;
		 	if(count($specialchars[0] > 0)) $strength += 30;
		 	
		 	$spec_count = sizeof($specialchars[0]);
		 	$number_count = count($numbers[0]);
		    
		    /** ------------------------------------ chars -----------*/
		    /*** check if password is not all lower case ***/
		    if(strtolower($password) != $password) {
		        $strength += 10; // was 1
		    }
		    
		    /*** check if password is not all upper case ***/
		    if(strtoupper($password) == $password) {
		        $strength += 10; // was 1
		    }
		    
		    /*** get the number of unique chars ***/
		    $chars = str_split($password);
		    $num_unique_chars = sizeof( array_unique($chars) );
		    
		    if($num_unique_chars < (($count-$spec_count-$number_count)/2)) $strength /= 3;
		    
		    //$this->debugVar((($spec_count)));
		    //$this->debugVar((($number_count)));
		    $strength += $num_unique_chars * 3;
		    
		   
		
		    /*** strength is a number 1-10; ***/
		    $strength = $strength > 99 ? 99 : $strength;
		    $strength = floor($strength / 10);
		
		    return $strength;
		}
		
		/**
	 	 * Return strength name and value
	 	 * @param $password
	 	 * @return array() 
	 	 */
		function getPasswordStrengthInfo($password) {
			if(!isset($this->password_strength)) $this->password_strength = new TextFunctionsPasswordStrength();
			
			$r = $this->password_strength->scorePwd($password);
			
			$r1 = array();
			$r1['averageScoreInfo'] = $this->_($r['averageScoreInfo']);
			$r1['averageScoreInfoNumber'] = $r['averageScoreInfoNumber'];
			return $r1;
		}
    	/**
	 	 * Return strength value 0-7
	 	 * @param $password
	 	 * @return int
	 	 */
		function getPasswordStrength($password) {
			if(!isset($this->password_strength)) $this->password_strength = new TextFunctionsPasswordStrength();
			
			$r = $this->password_strength->scorePwd($password);
			
			return $r['averageScoreInfoNumber'];
		}
		/**
		 * returnes Time from given string converted by $preg
		 * the preg_match formular has to include following strings
		 * h ... hour
		 * min ... minute
		 * s ... second
		 * d ... day
		 * m ... month
		 * y ... year 
		 * @param unknown_type $string
		 * @param unknown_type $preg
		 */
		public function getTimeFromString($string, $preg='') {
			$preg = ($preg == '') ? $this->config['default_rimg_preg'] : $preg;
			preg_match($preg, $string, $matches);
			
			if(isset($matches['h']) && 
				isset($matches['min']) &&
				isset($matches['s']) &&
				isset($matches['d']) &&
				isset($matches['m']) &&
				isset($matches['y'])){

				return mktime($matches['h'], $matches['min'], $matches['s'], $matches['m'], $matches['d'], $matches['y']);
				
			} else {
				return null;
			}
		}
		
		/**
		 * returnes Random Password
		 * @param $length
		 * @param $upperCaseCount
		 * @param $numberCount
		 * @param $specialCharCount
		 */
		public function generatePassword($length, $upperCaseCount=0, $numberCount=0, $specialCharCount=0){
			$chars = 'abcdefghijklmnopqrstuvwxyz';
			$uChars = strtoupper($chars);
			$numbers = '0123456789';
			//$specialChars = '!%$&-_#+*@()?=:;,./'; // do not add \ to specialChars (used as spacer from salt)
			$specialChars = '|!@#$%&*\/=?\-_+^():;,.';
			
			$out = '';
			
			$l = max($length - $upperCaseCount - $numberCount - $specialCharCount, 0);
			
			$tmp = array();
			
			for($i = 0; $i < $l; $i++) {
	        	array_push($tmp, substr($chars, mt_rand(0, strlen($chars) - 1), 1));
	     	}
	     	for($i = 0; $i < $upperCaseCount; $i++) {
	        	array_push($tmp, substr($uChars, mt_rand(0, strlen($uChars) - 1), 1));
	        }
	        for($i = 0; $i < $numberCount; $i++) {
	        	array_push($tmp, substr($numbers, mt_rand(0, strlen($numbers) - 1), 1));
	       	}
	        for($i = 0; $i < $specialCharCount; $i++) {
	        	array_push($tmp, substr($specialChars, mt_rand(0, strlen($specialChars) - 1), 1));
	        }

	        shuffle($tmp);
	        
	        return implode('', $tmp);
		}
    }
?>