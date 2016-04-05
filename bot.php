<?php
function processMessage($message) {
	// process incoming message
	$message_id = $message ['message_id']; // used in replies
	
	$chat_id = $message ['chat'] ['id']; // chat to send the message to
	
	switch ($message) {
		case array_key_exists ( 'text', $message ) :
			$text_message = $message ['text'];
			processTextMessage ( $text_message, $chat_id, $message_id );
			break;
		case array_key_exists ( 'audio', $message ) :
			break;
		case array_key_exists ( 'document', $message ) :
			break;
		case array_key_exists ( 'sticker', $message ) :
			break;
		case array_key_exists ( 'video', $message ) :
			break;
		case array_key_exists ( 'voice', $message ) :
			break;
		case array_key_exists ( 'contact', $message ) :
			break;
		case array_key_exists ( 'location', $message ) :
			break;
		default :
			break;
	}
}
function processTextMessage($text, $chat_id, $message_id) {
	switch ($text) {
		case "/start":
			startFunction ( $chat_id, $message_id );
			break;
		case "/stop":
			break;
		case "/occupation":
			occupationOfTheDay ( $chat_id );
			break;
		default :
			sendMessage ( $chat_id, "Cool", array (
					"reply_to_message_id" => $message_id 
			) );
			break;
	}
}
function startFunction($chat_id, $message_id) {
	$file = fopen ( "start.txt", "r" );
	$response = fread ( $file, filesize ( "start.txt" ) );
	fclose ( $file );
	sendMessage ( $chat_id, $response, array (
			'parse_mode' => 'Markdown' 
	) );
}
function occupationOfTheDay($chat_id) {
	$day = date ( 'j' );
	$month = date ( 'n' );
	$year = date ( 'Y' );
	$time = data ( 'H:i:s', time () );
	$lastDate = getLastDate ();
	$splitted = explode ( '-', $lastDate );
	
	$url = 'https://www7.ceda.polimi.it/spazi/spazi/controller/OccupazioniGiornoEsatto.do?csic=MIA&categoria=D&tipologia=tutte&giorno_day=' . $day . '&giorno_month=' . $month . '&giorno_year=' . $year . '&jaf_giorno_date_format=dd%2FMM%2Fyyyy&evn_visualizza=Visualizza+occupazioni';
	$result = getHTMLCurlResponse ( $url );
	
	$domOfHTML = getDOMFromHTMLIDWithCSS ( $result, 'tableContainer', "spazi/table-MOZ.css" );
	
	$myfile = fopen ( "occupation.html", "w" );
	fwrite ( $myfile, $domOfHTML->saveHTML () );
	fclose ( $myfile );
	$fileNamePath = realpath ( 'occupation.html' );
	
	$cmdLine = 'xvfb-run --server-args="-screen 0, 1024x768x24" /var/www/telegrambot/webkit2png.py -o /var/www/telegrambot/occupation.png /var/www/telegrambot/occupation.html';
	shell_exec ( $cmdLine );
	
	sendNewFile ( "sendDocument", array (
			'chat_id' => $chat_id,
			'document' => new CURLFile ( $fileNamePath ) 
	) );
}
function getHTMLCurlResponse($url) {
	$options = array (
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36',
			CURLOPT_AUTOREFERER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_CONNECTTIMEOUT => 120,
			CURLOPT_TIMEOUT => 120 
	);
	$ch = curl_init ( $url );
	curl_setopt_array ( $ch, $options );
	$content = curl_exec ( $ch );
	curl_close ( $ch );
	return $content;
}
function getDOMFromHTMLIDWithCSS($page, $idToSelect, $cssFilePath) {
	$dom = new DOMDocument ();
	$internalErrors = libxml_use_internal_errors ( true );
	$dom->loadHTML ( $page );
	$my = $dom->getElementById ( $idToSelect );
	$newdom = new DOMDocument ();
	$cloned = $my->cloneNode ( TRUE );
	$styleFile = fopen ( $cssFilePath, "r" );
	$style = fread ( $styleFile, filesize ( $cssFilePath ) );
	fclose ( $styleFile );
	$element = $newdom->createElement ( 'style', $style );
	$newdom->appendChild ( $element );
	$newdom->appendChild ( $newdom->importNode ( $cloned, TRUE ) );
	libxml_use_internal_errors ( $internalErrors );
	return $newdom;
}
?>