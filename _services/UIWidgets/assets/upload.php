<?php
/**
* upload.php
*
* Copyright 2011, Immanuel Bauer 
* based on code from Moxiecode Systems AB
* Released under GPL License.
*/

class Uploader {
	
	function __construct(){
		$GLOBALS['config']['root'] = substr(dirname(__FILE__), 0, -26);
		require_once $GLOBALS['config']['root'].'_services/UIWidgets/config.UIWidgets.php';
	}
	
	function checkUpload(){
		//$log = fopen($GLOBALS['config']['root'].'uploadlog.txt', 'a');
		
		// Get parameters
		$chunk = isset($_REQUEST['chunk']) ? $_REQUEST['chunk'] : 0;
		$chunks = isset($_REQUEST['chunks']) ? $_REQUEST['chunks'] : 0;
		$fileName = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
		
		//$headers = apache_request_headers();
		//foreach ($headers as $header => $value) {
		//    fwrite($log, "\r\n".$header.':'.$value);
		//}

		//fwrite($log, "\r\nuploading file:".$fileName.'('.$chunk.' of '.$chunks.')');
		
		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '', $fileName);
		
		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($this->config['tmpFolder'] . $fileName)) {
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);
		
			$count = 1;
			while (file_exists($this->config['tmpFolder'] . $fileName_a . '_' . $count . $fileName_b))
			$count++;
		
			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}
		
		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"])) $contentType = $_SERVER["HTTP_CONTENT_TYPE"];
		
		if (isset($_SERVER["CONTENT_TYPE"])) $contentType = $_SERVER["CONTENT_TYPE"];
		
		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			//fwrite($log, "\r\nmode: multipart ".(isset($_FILES['Filedata']['tmp_name'])?'found':'not found'));
			if (isset($_FILES['Filedata']['tmp_name']) && is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
				//fwrite($log, "\r\ncopy data");
				
				// Open temp file
				$out = fopen($this->config['tmpFolder'] . $fileName, $chunk == 1 ? "wb" : "ab");
				if ($out) {
					//fwrite($log, "\r\ntmp found");
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['Filedata']['tmp_name'], "rb");
		
					if ($in) {
						while ($buff = fread($in, 4096))
						fwrite($out, $buff);
					} else die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					//fwrite($log, "\r\ncopied data");
					fclose($in);
					fclose($out);
					@unlink($_FILES['Filedata']['tmp_name']);
				} else die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file.'.$_FILES['Filedata']['tmp_name'].'"}, "id" : "id"}');
		} else {
			//fwrite($log, "\r\nmode: stream");
			//fwrite($log, "\r\ndata: ".file_get_contents('php://input'));
			// Open temp file
			$out = fopen($this->config['tmpFolder'] . $fileName, $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");
		
				if ($in) {
					while ($buff = fread($in, 4096))
					fwrite($out, $buff);
				} else die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
		
				fclose($in);
				fclose($out);
			} else die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
		
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : "null", "id" : "id", "fileName" : "'.$fileName.'"}');
		
	}
	
}

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('content-type: application/json; charset=utf-8');

$up = new Uploader();
$up->checkUpload();


?>