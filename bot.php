<?php
/**
 * Process the incoming message
 * 
 * @param String $message the message received to process
 */
function processMessage($message) {
	$message_id = $message ['message_id']; // used in replies
	$chat_id = $message ['chat'] ['id'];
	$response_id = - 1; // chat to send the message to
	if (isset ( $message ['reply_to_message'] )) {
		$response_id = $message ['reply_to_message'] ['message_id'];
	}
	
	// checks what type of message is incoming and perform the correct operation
	switch ($message) {
		case array_key_exists ( 'text', $message ) :
			$text_message = $message ['text'];
			$text_message = str_replace ( "@PoliMilanoBot", "", $text_message );
			processTextMessage ( $text_message, $chat_id, $message_id, $response_id );
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
function processTextMessage($text, $chat_id, $message_id, $response_id) {
	$command = explode ( " ", $text );
	switch ($command [0]) {
		case "/start" :
			startFunction ( $chat_id, $message_id );
			break;
		case "/help" :
			startFunction ( $chat_id, $message_id );
			break;
		case "/stop" :
			break;
		case "/occupation" :
			if (isset ( $command [1] )) {
				occupationOfTheDay ( $chat_id, $command [1] );
			} else {
				occupationOfTheDay ( $chat_id, date ( "j" ) . "-" . date ( "n" ) . "-" . date ( "Y" ) );
			}
			break;
		case "/classroom" :
			if (! isset ( $command [1] )) {
				sendMessage ( $chat_id, "You must give me a Class !!!!!!", array (
						"reply_to_message_id" => $message_id 
				) );
			} else {
				if (isset ( $command [2] )) {
					classOccupation ( $chat_id, $command [1], $command [2] );
				} else {
					classOccupation ( $chat_id, $command [1], date ( "j" ) . "-" . date ( "n" ) . "-" . date ( "Y" ) );
				}
			}
			break;
		case "/free" :
			if (count ( $command ) == 1) {
				startNewFreeChat ( $chat_id, $message_id );
			}
			if (count ( $command ) == 2) {
				$file = fopen ( "responses/free.txt", "r" );
				$response = fread ( $file, filesize ( "responses/free.txt" ) );
				fclose ( $file );
				sendMessage ( $chat_id, $response, array (
						'parse_mode' => 'Markdown' 
				) );
			} else if (isset ( $command [3] )) {
				classFree ( $chat_id, $command [1], $command [2], $command [3], $message_id );
			} else if (count ( $command ) == 3) {
				classFree ( $chat_id, $command [1], $command [2], date ( "j" ) . "-" . date ( "n" ) . "-" . date ( "Y" ), $message_id );
			}
			break;
		default :
			parseFreeMessage ( $chat_id, $message_id, $response_id, $command [0] );
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
	$filePath = "./responses/start.txt";
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
function occupationOfTheDay($chat_id, $time) {
	$time = fixDayString ( $time );
	$date = strtotime ( $time );
	$filePath = "files/occupation" . $date . ".html";
	$result = true;
	if (! file_exists ( $filePath ) && (time () - filemtime ( $filePath ) > 3600 * 2)) {
		try {
			createOccupationFile ( $date, $filePath );
		} catch ( Exception $e ) {
			sendMessage ( $chat_id, "I've encountered a error in the Polimi Server. It's not my fault ;)", array () );
			error_log ( "Error creating file in function occupationOfTheDay" );
			$result = false;
			return;
		}
	}
	$result = sendFile ( $chat_id, $filePath, array (
			"caption" => "Occupation of " . date ( "l d-F", $date ) 
	) );
	if ($result === false) {
		sendMessage ( $chat_id, "I've encountered some troubles in sending you the HTML file, I'm Sorry !!", array () );
		error_log ( "Error while sending the file" . $filePath );
		return;
	}
}
/**
 * Creates the HTML file with the occupation for the specified date
 *
 * @param String $time
 *        	the day to create the file
 * @return false if an error occours creating the file otherwise returns true
 */
function createOccupationFile($date, $file_path) {
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
	$result = cUrlRequest ( $url, $options );
	if ($result == false) {
		error_log ( "Error calling cUrlRequest from createOccupationFile function" );
		throw new Exception ( "Error calling cUrlRequest from createOccupationFile function" );
		return;
	}
	
	$domOfHTML = getDOMFromHTMLIdWithCSS ( $result, 'tableContainer', "spazi/table-MOZ.css" );
	
	$file = fopen ( $file_path, "w" );
	fwrite ( $file, $domOfHTML->saveHTML () );
	fclose ( $file );
	return true;
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
/**
 * Creates and send an image of the occupation for the requested classroom
 *
 * @param int $chat_id
 *        	chat id to send the message to
 * @param String $className
 *        	name of the requested classroom
 * @param String $date
 *        	date for the requested occupation
 */
function classOccupation($chat_id, $className, $date) {
	$date = fixDayString ( $date );
	$time = strtotime ( $date );
	$className = str_replace ( ".", "", $className );
	$className = strtoupper ( $className );
	$classId = idOfGivenClassroom ( $className );
	if ($classId != - 1) {
		$cookieUrl = "https://www7.ceda.polimi.it/spazi/spazi/controller/Aula.do?evn_init=event&idaula=" . $classId . "&jaf_currentWFID=main";
		getCookies ( $cookieUrl );
		$url = "https://www7.ceda.polimi.it/spazi/spazi/controller/Aula.do?idaula=" . $classId . "&fromData_day=" . date ( "j", $time ) . "&fromData_month=" . date ( "n", $time ) . "&fromData_year=" . date ( "Y", $time ) . "&jaf_fromData_date_format=dd%2FMM%2Fyyyy&toData_day=" . date ( "j", $time ) . "&toData_month=" . date ( "n", $time ) . "&toData_year=" . date ( "Y", $time ) . "&jaf_toData_date_format=dd%2FMM%2Fyyyy&evn_occupazioni=Visualizza+occupazioni";
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
				CURLOPT_COOKIEFILE => realpath ( "cookie.txt" ) 
		);
		$response = cUrlRequest ( $url, $options );
		if ($response == false) {
			sendMessage ( $chat_id, "I've encountered a error in the Polimi Server. It's not my fault ;)", array () );
		} else {
			$domOfHTML = getDOMFromHTMLIDWithCSS ( $response, 'tableContainer', "spazi/table-MOZ.css" );
			$myfile = fopen ( "./files/" . $classId . ".html", "w" );
			fwrite ( $myfile, $domOfHTML->saveHTML () );
			fclose ( $myfile );
			$cmdLine = "/var/www/telegrambotbin/wkhtmltoimage --quality 30 --load-error-handling ignore /var/www/telegrambot/files/" . $classId . ".html /var/www/telegrambot/files/" . $classId . ".jpeg";
			shell_exec ( $cmdLine );
			$filePath = realpath ( "files/" . $classId . '.jpeg' );
			$photoResponse = sendPhoto ( $chat_id, $filePath, array () );
			if ($photoResponse === false) {
				error_log ( "Error in sending Photo for class occupation" );
			}
		}
	} else
		sendMessage ( $chat_id, "The given classroom not exits or doesn't match the Polimi Standard: ex D.2.1,L.26.13,N.0.2", array () );
}
/**
 * Creates the list of free class in a given day
 *
 * @param int $chat_id
 *        	the chat id to send the message to
 * @param String $startTime
 *        	the time used as a start time for the search
 * @param String $endTime
 *        	the time used as an end time for the search
 * @param String $date_sent
 *        	the date used to make the search
 */
function classFree($chat_id, $startTime, $endTime, $date_sent, $message_id) {
	$date_sent = fixDayString ( $date_sent );
	$date = strtotime ( $date_sent );
	$url = "https://www7.ceda.polimi.it/spazi/spazi/controller/RicercaAuleLibere.do?jaf_currentWFID=main";
	$param = array (
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___postBack' => 'true',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___formMode' => 'FILTER',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___categoriaScelta' => 'D',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___tipologiaScelta' => 'tutte',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___sede' => 'MIA',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___iddipScelto' => 'tutti',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___sigla' => '',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___giorno_day' => date ( "j", $date ),
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___giorno_month' => date ( "n", $date ),
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___giorno_year' => date ( "Y", $date ),
			'jaf_spazi___model___formbean___RicercaAvanzataAuleLibereVO___giorno_date_format' => 'dd/MM/yyyy',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___orario_dal' => $startTime,
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___orario_al' => $endTime,
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___soloPreseElettriche_default' => 'N',
			'spazi___model___formbean___RicercaAvanzataAuleLibereVO___soloPreseDiRete_default' => 'N',
			'evn_ricerca_avanzata' => 'Ricerca aule libere' 
	);
	$boundary = "----WebKitFormBoundary6baWbSkLbdhksRAi";
	$query = multipart_build_query ( $param, $boundary );
	$options = array (
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36',
			CURLOPT_AUTOREFERER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_CONNECTTIMEOUT => 120,
			CURLOPT_TIMEOUT => 120,
			CURLOPT_POSTFIELDS => $query,
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER => array (
					"Content-Type: multipart/form-data; boundary=" . $boundary,
					"Content-Length: " . strlen ( $query ) 
			) 
	);
	$result = cUrlRequest ( $url, $options );
	
	// Create the document with only the needed table
	$dom = new DOMDocument ();
	$internalErrors = libxml_use_internal_errors ( true );
	if (! (strlen ( $result ) == 0)) {
		$dom->loadHTML ( $result );
		$selection = $dom->getElementById ( "div_table_aule" );
		$newdom = new DOMDocument ();
		$cloned = $selection->cloneNode ( TRUE );
		$newdom->appendChild ( $newdom->importNode ( $cloned, TRUE ) );
		$finder = new DomXPath ( $newdom );
		$nodes = $finder->query ( '//tbody[@class="TableDati-tbody"]' );
		$node = $nodes->item ( 0 );
		
		// extract the list of available classroom
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
				"reply_to_message_id" => $message_id,
				'parse_mode' => 'Markdown',
				'reply_markup' => array (
						'hide_keyboard' => true,
						'selective' => true 
				) 
		) );
	} else {
		sendMessage ( $chat_id, "I've encountered a error in the Polimi Server. It's not my fault ;)", array (
				"reply_to_message_id" => $message_id,
				'parse_mode' => 'Markdown',
				'reply_markup' => array (
						'hide_keyboard' => true,
						'selective' => true 
				) 
		) );
	}
}

/**
 * Request a page and saves all the cookies in a file named cookies.tx in
 * netscape format
 *
 * @param String $url
 *        	the url to make the GET request
 */
function getCookies($url) {
	shell_exec ( "touch cookie.txt" );
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
			CURLOPT_COOKIEJAR => realpath ( "cookie.txt" ) 
	);
	$response = cUrlRequest ( $url, $options );
}
/**
 * Builds a POST query used to make a POST
 *
 * @param array $fields
 *        	all the params of the query
 * @param String $boundary
 *        	boundary to separate query fields
 * @return string the complete POST request
 */
function multipart_build_query($fields, $boundary) {
	$retval = '';
	foreach ( $fields as $key => $value ) {
		$retval .= "--$boundary\nContent-Disposition: form-data; name=\"$key\"\n\n$value\n";
	}
	$retval .= "--$boundary--";
	return $retval;
}

/**
 * This function returns the Date String passed in the right format dd-mm-yyyy
 *
 * @param unknown $unfixedDate        	
 * @return mixed
 */
function fixDayString($unfixedDate) {
	$newString = str_replace ( "/", "-", $unfixedDate );
	$newString = str_replace ( ".", "-", $newString );
	return $newString;
}

/**
 * This function send to the $chat_id and $message_id the first keyboard in order to start the the new command free
 * with keyboard
 *
 * @param unknown $chat_id        	
 * @param unknown $message_id        	
 */
function startNewFreeChat($chat_id, $message_id) {
	$objArray = retriveObject ( "objects.txt" );
	$parameters = array (
			"chat_id" => $chat_id 
	);
	$newObj = new freeObj ( $parameters );
	$messageSent = sendMessage ( $chat_id, "Please Select the startTime Hour", array (
			"reply_to_message_id" => $message_id,
			"reply_markup" => array (
					"keyboard" => array (
							getArrayForKeyboard ( "responses/hours.txt" ) 
					),
					"one_time_keyboard" => false,
					'resize_keyboard' => true,
					"selective" => true 
			) 
	) );
	$messageSent = $messageSent ["result"];
	$newMessageId = $messageSent ["message_id"];
	$newObj->setMessage_id ( $newMessageId );
	array_push ( $objArray, $newObj );
	serializeObject ( $objArray, "objects.txt" );
}

/**
 * This function do the parsing of the message without /, if they are a response of a free keyBoard, it either send the
 * next keybord or the list of free classrooms
 *
 * @param
 *        	$chat_id
 * @param
 *        	$message_id
 * @param
 *        	$replay_message
 * @param
 *        	$text
 */
function parseFreeMessage($chat_id, $message_id, $replay_message, $text) {
	$objArray = retriveObject ( "objects.txt" );
	$found = false;
	foreach ( $objArray as $key => $obj ) {
		$idToCompare = $obj->getMessage_id ();
		$chatToCompare = $obj->getChat_id ();
		if (($idToCompare == $replay_message or $replay_message == - 1) and ($chatToCompare == $chat_id)) {
			$returnValue = $obj->addProperty ( $text );
			$found = true;
			if (is_bool ( $returnValue )) {
				$obj->setMessage_id ( $message_id );
				$result = $obj->executeCommandFree ();
				unset ( $objArray [$key] );
				if (! $result) {
					sendMessage ( $chat_id, "You gimme some wrong informations", array (
							"reply_to_message_id" => $message_id,
							'reply_markup' => array (
									'hide_keyboard' => true,
									'selective' => true 
							) 
					) );
				}
			} else {
				$stringResult = explode ( " ", $returnValue );
				$keyboard = "responses/" . $stringResult [1] . ".txt";
				$messageSent = sendMessage ( $chat_id, "Please Select the " . $returnValue, array (
						"reply_to_message_id" => $message_id,
						"reply_markup" => array (
								"keyboard" => array (
										getArrayForKeyboard ( $keyboard ) 
								),
								"one_time_keyboard" => false,
								'resize_keyboard' => true,
								"selective" => true 
						) 
				) );
				$messageSent = $messageSent ["result"];
				$newMessageId = $messageSent ["message_id"];
				$obj->setMessage_id ( $newMessageId );
			}
			break;
		}
	}
	if ($found) {
		serializeObject ( $objArray, "objects.txt" );
	} else {
		unknown_Message ( $chat_id, $message_id );
	}
}
/**
 * Answers with an unknown answe message if the command is not recognised
 *
 * @param int $chat_id
 *        	the chat id to send the message to
 * @param int $message_id
 *        	the messsage id if this is a response
 */
function unknown_Message($chat_id, $message_id) {
	sendMessage ( $chat_id, "Sorry, I don't know this command :( Use /help for more information", array (
			"reply_to_message_id" => $message_id,
			'reply_markup' => array (
					'hide_keyboard' => true,
					'selective' => true 
			) 
	) );
}
?>