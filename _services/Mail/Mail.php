<?php

class Mail extends Service implements IService  {

	function __construct(){
		$this->name = 'Mail';
        $this->ini_file = $GLOBALS['to_root'].'_services/Mail/Mail.ini';
        parent::__construct();
	}

	public function view($args) {
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

	}

	/**
	* Sends a mail with the given content to the given recipient
	* @param string $to A single or multiple comma seperated email addresses. 
	* @param string $subject The subject of the mail
	* @param string $text The (html) content of the mail. Will be utf8 encoded.
	* @param string $from The email and name of the sender. e.g. "Mailer <mailer@yourdomain.com>"
	*/
	public function send($to, $subject, $text, $from=''){
		if($from == '') $from = $this->_setting('default.sender_adress').'@'.$_SERVER['SERVER_NAME'];
		
		//set headers
 		$headers = "MIME-Version: 1.0" . PHP_EOL;
    	$headers .= "Content-Type: text/html; charset=ISO-8859-1" . PHP_EOL;
		$headers .= 'From: ' .$from .  PHP_EOL;
	
		//send
		return mail($to, $subject, utf8_encode($text), $headers);
	}
}

?>