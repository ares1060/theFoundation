<?php

/**
 * This is a class for handling Paypal payments by implementing the Paypal NVP api.
 */
class PaypalSession {
	
	private $apiUser;
	private $apiPwd;
	private $apiSig;
	private $urlStub;
	
	private $returnUrl;
	private $cancelUrl;
	
	private $apiInterface = 'https://api-3t.paypal.com/nvp';
	private $paypalUrl = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
	
	/**
	 * Creates a new PaypalSession with the given api credentials
	 * @param string $apiUser The Paypal api username
	 * @param string $apiPwd The Paypal api password
	 * @param string $apiSig The Paypal api signature
	 */
	function __construct($apiUser, $apiPwd, $apiSig) {
		$this->apiUser = $apiUser;
		$this->apiPwd = $apiPwd;
		$this->apiSig = $apiSig;
		$this->urlStub = 'USER='.urlencode($apiUser).'&PWD='.urlencode($apiPwd).'&SIGNATURE='.urlencode($apiSig).'&VERSION=63.0';
	}
	
	/**
	 * Starts a Paypal payment session and forwards to Paypal.
	 * @param array $items A List of item names that should be paid for.
	 * @param array $counts A List of amounts per item.
	 * @param array $amountsPerItem A List of prices for a single item.
	 */
	public function startPayment($items, $counts, $amountsPerItem){
		$out = $this->setExpressCheckout($items, $counts, $amountsPerItem);
		if($out['ACK'] == 'Success'){
			//TODO store session to database
			
			//redirect
			redirectToPayPal($out['TOKEN']);
		}
	}
	
	/**
	* Finishes a Paypal payment session.
	* @param string $token The Paypal payment token.
	* @param string $payerID The Paypal ID of the payer.
	*/
	public function finishPayment($token, $payerID){
		
		//TODO retrieve info from database
		$items = array();
		$counts = array();
		$amountsPerItem = array();
		
		if($details = checkExpressCheckoutDetails($token, $payerID, $items, $counts, $amountsPerItem)){

			//TODO update buysession -> payment status & payer info
		
			$payResponse = doExpressCheckoutPayment($token, $payerID, $items, $counts, $amountsPerItem);
	
			if($payResponse['PAYMENTINFO_0_TRANSACTIONID'] != ''){
				//payment successfull
				//TODO update buysession -> status & transactionid
			} else {
				//payment error
				//TODO update buysession -> status
			}
		} else {
			//payment error
			//TODO update buysession -> status
		}
	}
	
	/**
	* Cancels a Paypal payment session.
	* @param string $token The Paypal payment token.
	*/
	public function cancelPayment($token){
		//TODO update buysession -> status
	}
	
	/**
	 * Registers the payment transaction with Paypal.
	 * @param array $items A List of item names that should be paid for.
	 * @param array $counts A List of amounts per item.
	 * @param array $amountsPerItem A List of prices for a single item.
	 */
	public function setExpressCheckout($items, $counts, $amountsPerItem){
		$itemUrlPart = '';
		$totalAmt = 0;
		$count = count($items);
		for($c = 0; $c < $count; $c++) {
			$itemUrlPart .= '&L_PAYMENTREQUEST_0_NAME'.$c.'='.urlencode($items[$c]);
			$itemUrlPart .= '&L_PAYMENTREQUEST_0_AMT'.$c.'='.urlencode($amountsPerItem[$c]);
			$itemUrlPart .= '&L_PAYMENTREQUEST_0_QTY'.$c.'='.urlencode($counts[$c]);
			$totalAmt += $counts[$c]*$amountsPerItem[$c];
		}
	
		//compile url
		$url = $this->urlStub.'&METHOD=SetExpressCheckout&CANCELURL='.urlencode($this->cancelUrl).'&RETURNURL='.urlencode($this->returnUrl).'&PAYMENTREQUEST_0_AMT='.urlencode($totalAmt).'&PAYMENTREQUEST_0_CURRENCYCODE=EUR'.$itemUrlPart;
		$response = $this->sendRequest($url);
	
		return $response;
	}
	
	
	/**
	 * Checks if the payment details fit the Paypal transaction token.
	 * @param string $token The Paypal payment token.
	 * @param string $payerID The Paypal ID of the payer.
	 * @param array $items A List of item names that should be paid for.
	 * @param array $counts A List of amounts per item.
	 * @param array $amountsPerItem A List of prices for a single item.
	 */
	function checkExpressCheckoutDetails($token, $payerID, $items, $counts, $amountsPerItem){
	
		//compile url
		$url = $this->urlStub.'&METHOD=GetExpressCheckoutDetails&TOKEN='.urlencode($token);
		$response = $this->sendRequest($url);
	
		//compare
		$totalAmt = 0;
		$count = count($items);
		$ok = true;
		if($response['TOKEN'] == $token && $response['PAYERID'] == $payerID){
			for($c = 0; $c < $count; $c++) {
				if(!$response['L_PAYMENTREQUEST_0_NAME'.$c] == $items[$c]){
					$ok = false;
					break;
				} else if(!$response['L_PAYMENTREQUEST_0_AMT'.$c] == $amountsPerItem[$c]){
					$ok = false;
					break;
				} else if(!$response['L_PAYMENTREQUEST_0_QTY'.$c] == $counts[$c]){
					$ok = false;
					break;
				}
					
				$totalAmt += $counts[$c]*$amountsPerItem[$c];
			}
			if(!$totalAmt == $response['PAYMENTREQUEST_0_AMT']) $ok = false;
			else if(!$response['PAYMENTREQUEST_0_CURRENCYCODE'] == 'EUR') $ok = false;
		} else $ok = false;
	
		//doPayment
		if($ok) return $response;
		else return false;
	}
	
	
	/**
	 * Completes the Paypal payment transaction.
	 * @param string $token The Paypal payment token.
	 * @param string $payerID The Paypal ID of the payer.
	 * @param array $items A List of item names that should be paid for.
	 * @param array $counts A List of amounts per item.
	 * @param array $amountsPerItem A List of prices for a single item.
	 */
	function doExpressCheckoutPayment($token, $payerID, $items, $counts, $amountsPerItem){
	
		$itemUrlPart = '';
		$totalAmt = 0;
		$count = count($items);
		for($c = 0; $c < $count; $c++) {
			$itemUrlPart .= '&L_PAYMENTREQUEST_0_NAME'.$c.'='.urlencode($items[$c]);
			$itemUrlPart .= '&L_PAYMENTREQUEST_0_AMT'.$c.'='.urlencode($amountsPerItem[$c]);
			$itemUrlPart .= '&L_PAYMENTREQUEST_0_QTY'.$c.'='.urlencode($counts[$c]);
			$totalAmt += $counts[$c]*$amountsPerItem[$c];
		}
	
		//compile url
		$url = $this->urlStub.'&METHOD=DoExpressCheckoutPayment&TOKEN='.urlencode($token).'&PAYERID='.$payerID.'&PAYMENTREQUEST_0_AMT='.urlencode($totalAmt).'&PAYMENTREQUEST_0_CURRENCYCODE=EUR'.$itemUrlPart.'&PAYMENTREQUEST_0_PAYMENTACTION=Sale';
		$response = $this->sendRequest($url);
	
		return $response;
	}
	
	/**
	 * A helperfunction for sending requests to the Paypal api.
	 * @param string $request The request string.
	 */
	private function sendRequest($request){

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->apiInterface);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
	
		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	
		//getting response from server
		$response = curl_exec($ch);
	
		if(!$response) echo curl_error($ch);
	
		curl_close($ch);
	
		return responseToArray($response);
	}
	
	/**
	 * Helper function to redirect to paypal via setting a new Location in the header.
	 * @param string $token The Paypal transaction token.
	 */
	public function redirectToPayPal($token){
		$url = $this->paypalUrl . $token;
		header("Location: ".$url);
	}
	
	/**
	 * A helper function which converts a Paypal api response into an array.
	 * @param string $response The response from the Paypal api.
	 * @return array:string
	 */
	function responseToArray($response){
		$out = array();
	
		$parts = explode('&',$response);
		$pc = count($parts);
	
		for($c = 0; $c < $pc; $c++){
			$subParts = explode('=', $parts[$c]);
			$out[strtoupper($subParts[0])] = urldecode($subParts[1]);
		}
	
		return $out;
	}
	
/* * * * * * * * * * * * * * */
/*     GETTERS & SETTERS     */
/* * * * * * * * * * * * * * */
	
	/**
	 * The url Paypal will forward to if the payment was completed
	 * @param string $url
	 */
	public function setReturnUrl($url){
		$this->returnUrl = $url;
	}
	
	/**
	 * The url Paypal will forward to if the user cancels the payment.
	 * @param string $url
	 */
	public function setCancelUrl($url){
		$this->cancelUrl = $url;
	}
	
	/**
	 * The url Paypal api calls are sent to. Normally hasn't to be changed.
	 * @param string $url
	 */
	public function setApiUrl($url){
		$this->apiInterface = $url;
	}
	
	/**
	 * The url the user should be forwarded to to complete the payment. Normally hasn't to be changed.
	 * @param string $url
	 */
	public function setPaypalUrl($url){
		$this->paypalUrl = $url;
	}
	
}

?>