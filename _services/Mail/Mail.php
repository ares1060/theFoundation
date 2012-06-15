<?php

class Mail extends Service implements IService  {

	function __construct(){
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
	public function send($to, $subject, $text, $from){
	
		//set headers
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers .= 'From: ' .$from. "\r\n";
	
		//send
		mail($to, $subject, utf8_encode($text), $headers);
	}
}

?>