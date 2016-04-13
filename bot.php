<?php

/**
 * Process the incoming message
 * 
 * @param String $message the message received to process
 */
function processMessage($message) {
	$message_id = $message ['message_id']; // used in replies
	
	$chat_id = $message ['chat'] ['id']; // chat to send the message to
	                                     
	// checks what type of message is incoming and perform the correct operation
	switch ($message) {
		case array_key_exists ( 'text', $message ) :
			$text_message = $message ['text'];
			$text_message = str_replace ( "@PoliMilanoBot", "", $text_message );
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
/**
 * Parses the incoming text message and perform the approrpiate action
 *
 * @param String $text
 *        	the string containing the message to parse
 * @param int $chat_id
 *        	the chat id to reply to
 * @param int $message_id
 *        	the id of the message to reply to
 */
function processTextMessage($text, $chat_id, $message_id) {
	$command = explode ( " ", $text );
	switch ($command [0]) {
		case "/start" :
			startFunction ( $chat_id, $message_id );
			break;
		case "/stop" :
			break;
		case "/occupation" :
			occupationOfTheDay ( $chat_id, $command [1] );
			break;
		case "/classroom" :
			if (! isset ( $command [1] )) {
				sendMessage ( $chat_id, "Stronzo mi devi dire un'aula", array (
						"reply_to_message_id" => $message_id 
				) );
			} else {
				classOccupation ( $chat_id, $command [1], true );
			}
			break;
		case "/free" :
			switch (count ( $command )) {
				case 1 :
					sendMessage ( $chat_id, "Devi passare anche i due orari su cui eseguire la richiesta", array (
							"reply_to_message_id" => $message_id 
					) );
					break;
				case 2 :
					sendMessage ( $chat_id, "Manca un orario", array (
							"reply_to_message_id" => $message_id 
					) );
					break;
				case 3 :
					if ($command [1] > $command [2]) {
						sendMessage ( $chat_id, "Il primo orario deve essere inferiore rispetto al secondo orario", array (
								"reply_to_message_id" => $message_id 
						) );
						break;
					} else {
						classFree ( $chat_id, $command [1], $command [2], false );
						break;
					}
			}
			;
			break;
		case "/freed" :
			switch (count ( $command )) {
				case 1 :
					sendMessage ( $chat_id, "Devi passare anche i due orari su cui eseguire la richiesta", array (
							"reply_to_message_id" => $message_id 
					) );
					break;
				case 2 :
					sendMessage ( $chat_id, "Manca un orario", array (
							"reply_to_message_id" => $message_id 
					) );
					break;
				case 3 :
					if ($command [1] > $command [2]) {
						sendMessage ( $chat_id, "Il primo orario deve essere inferiore rispetto al secondo orario", array (
								"reply_to_message_id" => $message_id 
						) );
						break;
					} else {
						classFree ( $chat_id, $command [1], $command [2], true );
						break;
					}
			}
			;
			break;
		default :
			sendMessage ( $chat_id, "Cool", array (
					"reply_to_message_id" => $message_id 
			) );
			break;
	}
}

/**
 * Sends the welcome message
 *
 * @param int $chat_id
 *        	chat to send the message to
 * @param int $message_id
 *        	message to send the reply to
 */
function startFunction($chat_id, $message_id) {
	$filePath = "./spazi/start.txt";
	$file = fopen ( $filePath, "r" );
	$response = fread ( $file, filesize ( $filePath ) );
	fclose ( $file );
	sendMessage ( $chat_id, $response, array (
			'parse_mode' => 'Markdown' 
	) );
}

/**
 * Creates an HTML file containing the occupation of the specified day
 *
 * @param int $chat_id
 *        	the chat id to reply to
 * @param String $date
 *        	the date of the day to retrieve the occupation
 */
function occupationOfTheDay($chat_id, $date) {
	$filePath = "./files/occupation.html";
	
	if (! file_exists ( $filePath )) {
		$result = createOccupationFile ();
	}
	
	if (time () - filemtime ( $filePath ) > 3600 * 2) {
		$result = createOccupationFile ();
	}
	if ($result) {
		$send_result = sendFile ( $chatId, $filePath, array (
				"caption" => "Occupation of " . date ( "l d-F" ) 
		) );
	} else {
		error_log ( "Error creating file in function occupationOfTheDay" );
	}
	if ($send_result === false) {
		error_log ( "Error while sending the file" . $filePath );
	}
	// $cmdLine = 'xvfb-run --server-args="-screen 0, 1024x768x24" /var/www/telegrambot/webkit2png.py -o /var/www/telegrambot/occupation.png /var/www/telegrambot/occupation.html';
	// shell_exec ( $cmdLine );
}
/**
 * Creates the HTML file with the occupation for the specified date
 *
 * @param String $time
 *        	the day to create the file
 * @return false if an error occours creating the file otherwise returns true
 */
function createOccupationFile($time) {
	$date = strtotime ( $time );
	$url = 'https://www7.ceda.polimi.it/spazi/spazi/controller/OccupazioniGiornoEsatto.do?csic=MIA&categoria=D&tipologia=tutte&giorno_day=' . date ( "j", $date ) . '&giorno_month=' . date ( "n", $date ) . '&giorno_year=' . date ( "Y", $date ) . '&jaf_giorno_date_format=dd%2FMM%2Fyyyy&evn_visualizza=Visualizza+occupazioni';
	$options = array (
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36',
			CURLOPT_AUTOREFERER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_CONNECTTIMEOUT => 120,
			CURLOPT_TIMEOUT => 120 
	);
	$result = cUrlGetRequest ( $url, $options );
	echo ($url);
	
	if ($result == false) {
		error_log ( "Error calling cUrlGetRequest from createOccupationFile function" );
		return false;
	}
	
	$domOfHTML = getDOMFromHTMLIdWithCSS ( $result, 'tableContainer', "spazi/table-MOZ.css" );
	
	$file = fopen ( "./files/occupation.html", "w" );
	fwrite ( $file, $domOfHTML->saveHTML () );
	fclose ( $file );
	return true;
}
function getHTMLCurlResponse($url, $cookie) {
	/*
	 * $options = array (
	 * CURLOPT_RETURNTRANSFER => true,
	 * CURLOPT_HEADER => false,
	 * CURLOPT_FOLLOWLOCATION => true,
	 * CURLOPT_MAXREDIRS => 10,
	 * CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36',
	 * CURLOPT_AUTOREFERER => true,
	 * CURLOPT_ENCODING => "",
	 * CURLOPT_CONNECTTIMEOUT => 120,
	 * CURLOPT_TIMEOUT => 120
	 * );
	 *
	 * $ch = curl_init ( $url );
	 * curl_setopt_array ( $ch, $options );
	 */
	if (strlen ( $cookie ) > 0) {
		curl_setopt ( $ch, CURL_HTTPHEADER, array (
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
				"Accept-Language: en-US,en;q=0.5",
				"Accept-Encoding: gzip, deflate, br",
				"Cookie: " . $cookie,
				"Connection : keep-alive" 
		) );
	}
	$content = curl_exec ( $ch );
	curl_close ( $ch );
	return $content;
}
/**
 * Creates DOM document compiled with html and CSS
 *
 * @param String $page
 *        	the string containing HTML code
 * @param String $idToSelect
 *        	id of the element to extract from HTML
 * @param String $cssFilePath
 *        	path to the css to include
 * @return DOMDocument the dom document of the html and css combined
 */
function getDOMFromHTMLIdWithCSS($page, $idToSelect, $cssFilePath) {
	$dom = new DOMDocument (); // creates a new dom document
	$internalErrors = libxml_use_internal_errors ( true ); // to avoid logging warning for malformed HTML pages
	$dom->loadHTML ( $page ); // transform HTML to dom file
	$domElement = $dom->getElementById ( $idToSelect );
	$newDomWithSelectedId = new DOMDocument ();
	$cloned = $domElement->cloneNode ( TRUE );
	$styleFile = fopen ( $cssFilePath, "r" );
	$style = fread ( $styleFile, filesize ( $cssFilePath ) );
	fclose ( $styleFile );
	$element = $newDomWithSelectedId->createElement ( 'style', $style );
	$newDomWithSelectedId->appendChild ( $element );
	$newDomWithSelectedId->appendChild ( $newDomWithSelectedId->importNode ( $cloned, TRUE ) );
	libxml_use_internal_errors ( $internalErrors ); // restore normal logging method
	return $newDomWithSelectedId;
}
function classOccupation($chat_id, $className, $tomorrow) {
	$day = date ( "j" );
	$month = date ( "n" );
	$year = date ( "Y" );
	$classId = idOfGivenClassroom ( $className );
	if ($classId != - 1) {
		$cookieUrl = "https://www7.ceda.polimi.it/spazi/spazi/controller/Aula.do?evn_init=event&idaula=" . $classId . "&jaf_currentWFID=main";
		$cookies = getCookies ( $cookieUrl );
		$cookie = explode ( "; ", $cookies );
		$session = substr ( $cookie [0], 1 );
		$url = "https://www7.ceda.polimi.it/spazi/spazi/controller/Aula.do?idaula=" . $classId . "&fromData_day=" . $day . "&fromData_month=" . $month . "&fromData_year=" . $year . "&jaf_fromData_date_format=dd%2FMM%2Fyyyy&toData_day=" . $day . "&toData_month=" . $month . "&toData_year=" . $year . "&jaf_toData_date_format=dd%2FMM%2Fyyyy&evn_occupazioni=Visualizza+occupazioni";
		$response = getHTMLCurlResponse ( $url, $cookie );
		$dom = getDOMFromHTMLIDWithCSS ( $response, 'tableContainer', "spazi/table-MOZ.css" );
		$myfile = fopen ( $classId, "w" );
		fwrite ( $myfile, $domOfHTML->saveHTML () );
		fclose ( $myfile );
		$cmdLine = 'xvfb-run --server-args="-screen 0, 1024x768x24" /var/www/telegrambot/webkit2png.py -o /var/www/telegrambot/' . $classId . '.png /var/www/telegrambot/' . $classId;
		shell_exec ( $cmdLine );
		$fileNamePath = realpath ( $classId . '.png' );
		sendNewFile ( "sendPhoto", array (
				'chat_id' => $chat_id,
				'document' => new CURLFile ( $fileNamePath ) 
		) );
	} else
		sendMessage ( $chat_id, "La classe non esiste", array () );
}
function extractClassName($page) {
	$dom = new DOMDocument ();
	$internalErrors = libxml_use_internal_errors ( true );
	$dom->loadHTML ( $page );
	$finder = new DomXPath ( $dom );
	$classname = "TestoSX Dati1";
	$nodes = $finder->query ( "//td[contains(@class, '$classname')]" );
	error_log ( var_dump ( $nodes ) );
	// $class=$nodes[1][1];
	// return $classText;
}
/* function classFree($chat_id, $startTime, $endTime, $tomorrow) */
function classFree($chat_id, $startTime, $endTime, $time) {
	$date = strtotime ( $time );
	$day = date ( 'j' );
	$month = date ( 'n' );
	$year = date ( 'Y' );
	$url = "https://www7.ceda.polimi.it/spazi/spazi/controller/RicercaAuleLibere.do?jaf_currentWFID=main";
	$urlCookies = "https://www7.ceda.polimi.it/spazi/spazi/controller/RicercaAula.do?evn_ricerca_aule_libere=evento&jaf_currentWFID=main";
	$param = array (
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___postBack' => 'true',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___formMode' => 'FILTER',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___categoriaScelta' => 'D',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___tipologiaScelta' => 'tutte',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___sede' => 'MIA',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___iddipScelto' => 'tutti',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___sigla' => '',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___giorno_day' => $day,
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___giorno_month' => $month,
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___giorno_year' => $year,
			'jaf_spazi___model___formbean___RicercaAvanzataAuleLibereVO___giorno_date_format' => 'dd/MM/yyyy',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___orario_dal' => $startTime,
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___orario_al' => $endTime,
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___soloPreseElettriche_default' => 'N',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___soloPreseDiRete_default' => 'N',
			'evn_ricerca_avanzata' => 'Ricerca aule libere' 
	);
	$boundary = "---------------------------41989678077857697020580537542";
	$query = multipart_build_query ( $param, $boundary );
	$cookies = getCookies ( $urlCookies );
	$cookie = explode ( "; ", $cookies );
	$session = substr ( $cookie [0], 1 );
	$session = "_ga=GA1.2.112926790.1459958705; " . $session;
	$result = postHTMLCurlResponse ( $url, $query, $session );
	$dom = new DOMDocument ();
	$internalErrors = libxml_use_internal_errors ( true );
	$dom->loadHTML ( $result );
	$selection = $dom->getElementById ( "div_table_aule" );
	$newdom = new DOMDocument ();
	$cloned = $selection->cloneNode ( TRUE );
	$newdom->appendChild ( $newdom->importNode ( $cloned, TRUE ) );
	$finder = new DomXPath ( $newdom );
	$nodes = $finder->query ( '//tbody[@class="TableDati-tbody"]' );
	$node = $nodes->item ( 0 );
	$answer = "";
	if ($node->hasChildNodes ()) {
		$parents = $node->childNodes;
		$last = ($parents->length) - 1;
		foreach ( $parents as $i => $parent ) {
			$childs = $parent->childNodes;
			$string = "";
			foreach ( $childs as $j => $child ) {
				if ($j == 2) {
					if ($child->hasChildNodes ()) {
						$className = $child->childNodes->item ( 1 )->nodeValue;
						$string = $string . $className;
					}
				}
			}
			if (! ($i == $last)) {
				$answer = $answer . $string . "\n";
			} else {
				$answer = $answer . $string;
			}
		}
	}
	sendMessage ( $chat_id, $answer, array (
			'parse_mode' => 'Markdown' 
	) );
}
function postHTMLCurlResponse($url, $params, $cookieString) {
	$options = array (
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36',
			CURLOPT_AUTOREFERER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_CONNECTTIMEOUT => 120,
			CURLOPT_TIMEOUT => 120,
			CURLOPT_POSTFIELDS => $params,
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER => array (
					"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
					"Accept-Language: en-US,en;q=0.5",
					"Accept-Encoding: gzip, deflate, br",
					"Cookie: " . $cookieString,
					"Connection : keep-alive",
					"Cache-Control: max-age=0",
					"Content-Type: multipart/form-data; boundary=---------------------------41989678077857697020580537542",
					"Content-Length: " . strlen ( $params ) 
			) 
	);
	$ch = curl_init ( $url );
	curl_setopt_array ( $ch, $options );
	$content = curl_exec ( $ch );
	curl_close ( $ch );
	return $content;
}
/**
 * Request a page and saves all the cookies in a file named cookies.tx in
 * netscape format
 *
 * @param String $url
 *        	the url to make the GET request
 */
function getCookies($url) {
	$ch = curl_init ();
	$options = array (
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36',
			CURLOPT_AUTOREFERER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_CONNECTTIMEOUT => 120,
			CURLOPT_TIMEOUT => 120,
			CURLOPT_COOKIEJAR => dirname ( __FILE__ ) . "/cookie.txt" 
	);
	
	$response = cUrlGetRequest ( $url, $options );
}
function multipart_build_query($fields, $boundary) {
	$retval = '';
	foreach ( $fields as $key => $value ) {
		$retval .= "--$boundary\nContent-Disposition: form-data; name=\"$key\"\n\n$value\n";
	}
	$retval .= "--$boundary--";
	return $retval;
}
?>