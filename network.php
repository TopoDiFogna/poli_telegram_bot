<?php
// token of the bot to keep secret
define ( 'BOT_TOKEN', '142518261:AAGi48H9GL-oQxw_cQFVmwFnPVT6KBVFty0' );

// url to query the bot
define ( 'API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/' );
function exec_curl_request($handle) {
	
	// response to save response from telegram servers
	$response = curl_exec ( $handle );
	
	// if we get an error
	if ($response === false) {
		$errno = curl_errno ( $handle );
		$error = curl_error ( $handle );
		error_log ( "Curl returned error $errno: $error\n" );
		curl_close ( $handle );
		return false;
	}
	
	$http_code = intval ( curl_getinfo ( $handle, CURLINFO_HTTP_CODE ) );
	curl_close ( $handle );
	
	if ($http_code >= 500) { // internal server error
	                         // do not wat to DDOS server if something goes wrong
		sleep ( 10 );
		return false;
	} else if ($http_code != 200) { // we have an error //200=ok code
		
		$response = json_decode ( $response, true );
		error_log ( "Request has failed with error {$response['error_code']}: {$response['description']}\n" );
		
		if ($http_code == 401) { // 400=unauthorized access
			throw new Exception ( 'Invalid access token provided' );
		}
		
		return false;
	} else {
		// no error, so we parse the response
		$response = json_decode ( $response, true );
		
		if (isset ( $response ['description'] )) {
			
			error_log ( "Request was successfull: {$response['description']}\n" );
		}
		
		$response = $response ['result'];
	}
	return $response;
}
function apiRequestJson($method, $parameters) {
	if (! is_string ( $method )) {
		echo ("Method name must be a string\n");
		return false;
	}
	
	if (! $parameters) {
		$parameters = array ();
	} else if (! is_array ( $parameters )) {
		
		echo ("Parameters must be an array\n");
		
		return false;
	}
	
	$parameters ["method"] = $method;
	
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, API_URL );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_CONNECTTIMEOUT, 5 );
	curl_setopt ( $handle, CURLOPT_TIMEOUT, 60 );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, json_encode ( $parameters ) );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, array (
			"Content-Type: application/json" 
	) );
	return exec_curl_request ( $handle );
}

function apiRequestJsonFile($method, $parameters) {
	if (! is_string ( $method )) {
		echo ("Method name must be a string\n");
		return false;
	}

	if (! $parameters) {
		$parameters = array ();
	} else if (! is_array ( $parameters )) {
		echo ("Parameters must be an array\n");
		return false;
	}

	$parameters ["method"] = $method;

	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, API_URL );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt($handle, CURLOPT_POST,true);
	curl_setopt ( $handle, CURLOPT_CONNECTTIMEOUT, 5 );
	curl_setopt ( $handle, CURLOPT_TIMEOUT, 60 );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, json_encode ( $parameters ) );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, array (
			"Content-Type: multipart/form-data"
	) );
	return exec_curl_request ( $handle );
}
?>