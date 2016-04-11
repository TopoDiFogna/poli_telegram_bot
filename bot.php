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
	$command = explode ( " ", $text );
	switch ($command [0]) {
		case "/start" :
			startFunction ( $chat_id, $message_id );
			break;
		case "/stop" :
			break;
		case "/occupation" :
			occupationOfTheDay ( $chat_id );
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
function startFunction($chat_id, $message_id) {
	$file = fopen ( "start.txt", "r" );
	$response = fread ( $file, filesize ( "start.txt" ) );
	fclose ( $file );
	sendMessage ( $chat_id, $response, array (
			'parse_mode' => 'Markdown' 
	) );
}
function occupationOfTheDay($chat_id) {
	$fileNamePath = realpath ( 'occupation.html' );
	
	if (! file_exists ( $fileNamePath )) {
		createOccupationFile ();
	}
	
	if (time () - filemtime ( $fileNamePath ) > 3600 * 2) {
		createOccupationFile ();
	}
	
	// $cmdLine = 'xvfb-run --server-args="-screen 0, 1024x768x24" /var/www/telegrambot/webkit2png.py -o /var/www/telegrambot/occupation.png /var/www/telegrambot/occupation.html';
	// shell_exec ( $cmdLine );
	
	sendNewFile ( "sendDocument", array (
			'chat_id' => $chat_id,
			'document' => new CURLFile ( $fileNamePath ) 
	) );
}
function createOccupationFile() {
	$day = date ( 'j' );
	$month = date ( 'n' );
	$year = date ( 'Y' );
	$url = 'https://www7.ceda.polimi.it/spazi/spazi/controller/OccupazioniGiornoEsatto.do?csic=MIA&categoria=D&tipologia=tutte&giorno_day=' . $day . '&giorno_month=' . $month . '&giorno_year=' . $year . '&jaf_giorno_date_format=dd%2FMM%2Fyyyy&evn_visualizza=Visualizza+occupazioni';
	$result = getHTMLCurlResponse ( $url, "" );
	
	$domOfHTML = getDOMFromHTMLIDWithCSS ( $result, 'tableContainer', "spazi/table-MOZ.css" );
	
	$myfile = fopen ( "occupation.html", "w" );
	fwrite ( $myfile, $domOfHTML->saveHTML () );
	fclose ( $myfile );
}
function getHTMLCurlResponse($url, $cookie) {
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
	if (strlen ( $cookie ) > 0) {
		echo("sono nell'if");
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
				"Cookie: " . $cookie,
		));
	}
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
		$response = getHTMLCurlResponse ( $url, $session);
		$domOfHTML = getDOMFromHTMLIDWithCSS ( $response, 'tableContainer', "spazi/table-MOZ.css" );
		$myfile = fopen ( $classId, "w" );
		fwrite ( $myfile, $domOfHTML->saveHTML () );
		fclose ( $myfile );
		$cmdLine = 'xvfb-run --server-args="-screen 0, 1024x768x24" /var/www/telegrambot/webkit2png.py -o /var/www/telegrambot/' . $classId . '.png /var/www/telegrambot/' . $classId;
		shell_exec ( $cmdLine );
		$fileNamePath = realpath ( $classId . '.png' );
		sendNewFile ( "sendPhoto", array (
				'chat_id' => $chat_id,
				'photo' => new CURLFile ( $fileNamePath ) 
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
function classFree($chat_id, $startTime, $endTime, $tomorrow) {
	$day = date ( 'j' );
	if ($tomorrow) {
		$day = $day + 1;
	}
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
function getCookies($url) {
	// open a site with cookies
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_USERAGENT,  'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36' );
	curl_setopt ( $ch, CURLOPT_HEADER, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, 'cookie.txt' );
	$content = curl_exec ( $ch );
	
	// get cookies
	$cookies = array ();
	preg_match_all ( '/Set-Cookie:(?<cookie>\s{0,}.*)$/im', $content, $cookies );
	
	print_r ( $cookies ['cookie'] ); // show harvested cookies
	$cookieString = $cookies ['cookie'] [0];
	return $cookieString;
	// basic parsing of cookie strings (just an example)
	// $cookieParts = array();
	// preg_match_all('/Set-Cookie:\s{0,}(?P<name>[^=]*)=(?P<value>[^;]*).*?expires=(?P<expires>[^;]*).*?path=(?P<path>[^;]*).*?domain=(?P<domain>[^\s;]*).*?$/im', $content, $cookieParts);
	// print_r($cookieParts);
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