<?php
// url to query the bot
define ( 'API_URL', 'https://api.telegram.org/bot' );
/**
 * Creates the correct url for the bot
 * @return String the complete url used by the bot
 */
function createApiUrl() {
	$filePath = "token.txt";
	$file = fopen ( $filePath, "r" );
	$token = fread ( $file, filesize ( $filePath ) );
	fclose ( $file );
	$apiUrlToken = API_URL . $token;
	$apiUrlToken = str_replace("\n", "", $apiUrlToken);
	return $apiUrlToken;
}

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
/**
 * Send a message
 *
 * @param int $chatId
 *        	chat it to send the message to
 * @param String $text
 *        	the text message to be sent
 * @param array $parameters
 *        	additional parameters
 */
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
	$url = createApiUrl ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $handle, CURLOPT_CONNECTTIMEOUT, 5 );
	curl_setopt ( $handle, CURLOPT_TIMEOUT, 60 );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, json_encode ( $params ) );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, array (
			"Content-Type: application/json" 
	) );
	$response = execCUrlRequest ( $handle );
	$response = json_decode ( $response, true );
	return $response;
}
/**
 * Sends file
 *
 * @param int $chatId
 *        	chat it to send the message to
 * @param String $filePath
 *        	the file path to be sent or the corresponding file_id
 * @param array $parameters
 *        	additional parameters
 * @return boolean false if an error occurred, true otherwise
 */
function sendFile($chatId, $filePath, $params) {
	if (! is_array ( $params )) {
		error_log ( "Parameters must be an array in sendFile method" );
		return false;
	}
	
	$file = new CURLFile ( realpath ( $filePath ) );
	$params ["method"] = "sendDocument";
	$params ["chat_id"] = $chatId;
	$params ["document"] = $file;
	$handle = curl_init ();
	$url = createApiUrl ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $handle, CURLOPT_CONNECTTIMEOUT, 5 );
	curl_setopt ( $handle, CURLOPT_TIMEOUT, 60 );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, $params );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, array (
			"Content-Type: multipart/form-data" 
	) );
	$response = execCUrlRequest ( $handle );
	if ($response === false) {
		return false;
	}
	return $response;
}
/**
 * Sends compressed photo
 *
 * @param int $chatId
 *        	chat it to send the message to
 * @param String $filePath
 *        	the file path to be sent or the corresponding file_id
 * @param array $parameters
 *        	additional parameters
 * @return boolean false if an error occurred, true otherwise
 */
function sendPhoto($chatId, $filePath, $params) {
	if (! is_array ( $params )) {
		error_log ( "Parameters must be an array in sendPhoto method" );
		return false;
	}
	$file = new CURLFile ( $filePath );
	$params ["method"] = "sendPhoto";
	$params ["chat_id"] = $chatId;
	$params ["photo"] = $file;
	$handle = curl_init ();
	$url = createApiUrl ();
	curl_setopt ( $handle, CURLOPT_URL, $url );
	curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $handle, CURLOPT_CONNECTTIMEOUT, 5 );
	curl_setopt ( $handle, CURLOPT_TIMEOUT, 60 );
	curl_setopt ( $handle, CURLOPT_POSTFIELDS, $params );
	curl_setopt ( $handle, CURLOPT_HTTPHEADER, array (
			"Content-Type: multipart/form-data" 
	) );
	$response = execCUrlRequest ( $handle );
	if ($response === false) {
		return false;
	}
	return $response;
}
/**
 * Makes a cUrl request by a GET method
 *
 * @param array $params
 *        	the array containing curl opts
 * @return the response of the GET request
 */
function cUrlRequest($url, $params) {
	$ch = curl_init ( $url );
	curl_setopt_array ( $ch, $params );
	$response = execCUrlRequest ( $ch );
	return $response;
}
?>