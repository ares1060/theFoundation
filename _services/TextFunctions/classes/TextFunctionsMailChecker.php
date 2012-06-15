<?php
/**
 * checks if EMail Adress exists
 * @copyright http://creativecommons.org/licenses/by/2.0/ - Please keep this comment intact 
 * @author scrapy_ii@gmail.com - edited for theFoundation
 * @contributers adnan@barakatdesigns.net, gabe@fijiwebdesign.com 
 */
class TextFunctionsMailChecker {
	
	 /** 
	  * Maximum time to read from socket 
	  */  
	 const max_read_time = 5;  
	 /** 
	  * SMTP Port 
	  */  
	 const port = 25;  
	 /** 
	  * Maximum Connection Time to an MTA  
	  */  
	 const max_conn_time = 30;  
	
	/** 
	 * Validate Email Addresses 
	 * @param String $emails Emails to validate (recipient emails) 
	 * @param String $sender Sender's Email 
	 * @return Array Associative List of Emails and their validation results 
	 */  
	public static function validateEMail($email, $sender='a@localhost'){
		$results = array();  
  
		list($domain, $user) = self::_parseEMail($email);
		list($domainS, $userS) = self::_parseEMail($sender);
		
     	$mxs = array();  
    
		// retrieve SMTP Server via MX query on domain  
		list($hosts, $mxweights) = self::queryMX($domain);  
  
		// retrieve MX priorities  
		for($n=0; $n < count($hosts); $n++){  
			$mxs[$hosts[$n]] = $mxweights[$n];  
		}  
		asort($mxs);  
   
		// last fallback is the original domain  
		array_push($mxs, $domain);  
     
     	$timeout = self::max_connection_time/count($hosts); // max connection time = 30  
      
     	$sock = null;
   		// try each host 
   		while(list($host) = each($mxs)) {  
    		// connect to SMTP server  
    		if ($sock = fsockopen($host, self::port, $errno, $errstr, (float) $timeout)) {  
     			stream_set_timeout($sock, self::max_read_time);  
		    	return false;
    			break;  
    		}  
   		}  
    
   		// did we get a TCP socket  
   		if ($sock) {  
    		$reply = fread($sock, 2082);  
      
    		preg_match('/^([0-9]{3}) /ims', $reply, $matches);  
    		$code = isset($matches[1]) ? $matches[1] : '';  
   
   		 	if($code != '220') {  
     			// MTA gave an error... 
     			// quit  
		    	self::send("quit", $sock);  
		    	// close socket  
		    	fclose($sock);  
	     		return false;
	     		break;
   		 	}
    	} else {
    		return false;
    		break;
    	}
  
    	// say helo  
    	self::send("HELO ".$domainS, $sock);  
    	// tell of sender  
    	self::send("MAIL FROM: <".$userS.'@'.$domainS.">", $sock);  
      
     	// ask of recepient  
     	$reply = self::send("RCPT TO: <".$user.'@'.$domain.">", $sock);  
       
      	// get code and msg from response  
     	preg_match('/^([0-9]{3}) /ims', $reply, $matches);  
     	$code = isset($matches[1]) ? $matches[1] : '';  
    
     	$return = false;
     	
     	if ($code == '250') {  
      		// you received 250 so the email address was accepted  
      		$return =  true;
     	} elseif ($code == '451' || $code == '452') {  
   			// you received 451 so the email address was greylisted (or some temporary error occured on the MTA) - so assume is ok  
   			$return = true;
     	} else {  
      		$return =  false;
     	}  
      
    	// quit  
    	self::send("quit", $sock);  
    	// close socket  
    	fclose($sock); 

    	return $return;
 	}

	/**
	 * Parses Email and returnes $user and $domain
	 * @param unknown_type $email
	 */
 	private static function _parseEmail($email) {  
		$parts = explode('@', $email);  
		$domain = array_pop($parts);  
		$user= implode('@', $parts);  
		return array($user, $domain);  
	}  
	  
	 /** 
	  * Query DNS server for MX entries 
	  * @return  
	  */  
 	private static function queryMX($domain) {  
  		$hosts = array();  
 		$mxweights = array();  
  		if (function_exists('getmxrr')) {  
   			getmxrr($domain, $hosts, $mxweights);  
  		} /*
  		// windows
  		else {  
	   		// windows, we need Net_DNS  
	  		require_once 'Net/DNS.php';  
	  
	  		$resolver = new Net_DNS_Resolver();  
	  		$resolver->debug = $this->debug;  
	  		// nameservers to query  
	  		$resolver->nameservers = $this->nameservers;  
	  		$resp = $resolver->query($domain, 'MX');  
	  		if ($resp) {  
	   			foreach($resp->answer as $answer) {  
	    			$hosts[] = $answer->exchange;  
	    			$mxweights[] = $answer->preference;  
	   			}  
	 		}  
  		}  */
		return array($hosts, $mxweights);  
 	}  
 	
 	private static function send($msg, $sock) {  
  		fwrite($sock, $msg."\r\n");  
  
  		$reply = fread($sock, 2082);  
  
  		return $reply;  
 	}  
}
?>