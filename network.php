<?php
// token of the bot to keep secret
define ( 'BOT_TOKEN', '142518261:AAGi48H9GL-oQxw_cQFVmwFnPVT6KBVFty0' );

// url to query the bot
define ( 'API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/' );

/**
 * Makes a cUrl request
 *
 * @param cUrlHandle $handle
 *        	the handle to the cUrl request
 * @return boolean|mixed false if cUrl got some error, mixed otherwise
 */
function execCUrlRequest($handle) {
	$response = curl_exec ( $handle );
	// if we get an error
	if ($response === false) {
		
		$errno = curl_errno ( $handle );
		$error = curl_error ( $handle );
		curl_close ( $handle );
		return false;
	}
	
	$http_code = intval ( curl_getinfo ( $handle, CURLINFO_HTTP_CODE ) );
	curl_close ( $handle );
	
	if ($http_code >= 500) { // internal server error
		return false;
	} else if ($http_code != 200) { // we have an error //200=ok code
		
		$response = json_decode ( $response, true );
		error_log ( "Request has failed with error " . $http_code );
		
		if ($http_code == 401) { // 401=unauthorized access
			error_log ( "Invalid access token provided" );
		}
		
		return false;
	}
	return $response;
}
function sendMessage($chat_id, $text, $params) {
	if (! $params) {
		$params = array ();
	} elseif (! is_array ( $params )) {
		error_log ( "params is not an array!" );
	}
	$params ["method"] = "sendMessage";
	$params ["text"] = $text;
	$params ["chat_id"] = $chat_id;
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, API_URL );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_CONNECTTIMEOUT, 5 );
	curl_setopt ( $handle, CURLOPT_TIMEOUT, 60 );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, json_encode ( $params ) );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, array (
			"Content-Type: application/json" 
	) );
	execCUrlRequest ( $handle );
}
/**
 * Sends a general file, can be photo, video, document ecc
 * 
 * @param String $method the method appropriate to the file to be sent
 * @param String $file the file to be ent
 * @param array $parameters additional parameters
 * @return boolean|boolean|mixed
 */
function sendFile($method, $file, $parameters) {
	if (! is_string ( $method )) {
		error_log("Method in sendFile name must be a string");
		return false;
	}
	
	if (! $parameters) {
		$parameters = array ();
	} else if (! is_array ( $parameters )) {
		error_log("Parameters must be an array in sendFile method");
		return false;
	}
	
	$parameters ["method"] = $method;
	
	$handle = curl_init ();
	curl_setopt ( $handle, CURLOPT_URL, API_URL );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $handle, CURLOPT_CONNECTTIMEOUT, 5 );
	curl_setopt ( $handle, CURLOPT_TIMEOUT, 60 );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, $parameters );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, array (
			"Content-Type:multipart/form-data" 
	) );
	$response=execCUrlRequest ( $handle );
	if ($response === false) {
		return false;
	}
	return true;
}
/**
 * Makes a cUrl request by a GET method
 *
 * @param array $params
 *        	the array containing curl opts
 * @return the response of the GET request
 */
function cUrlGetRequest($url, $params) {
	$ch = curl_init ( $url );
	curl_setopt_array ( $ch, $params );
	$response = execCUrlRequest ( $ch );
	return $response;
}
?>