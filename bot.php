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
	if (isset ( $text )) {
		
		if (strpos ( $text, "/start" ) === 0) {
			startFunction ( $chat_id, $message_id );
		} else if ($text === "Hello" || $text === "Hi") {
			apiRequestJson ( "sendMessage", array (
					'chat_id' => $chat_id,
					"text" => 'Nice to meet you' 
			) );
		} else if (strpos ( $text, "/stop" ) === 0) {
			
			// stop now
		} else if (strpos ( $text, "/occupation" ) === 0) {
			occupationOfTheDay ( $chat_id, $message_id );
		} else {
			
			apiRequestJson ( "sendMessage", array (
					'chat_id' => $chat_id,
					"reply_to_message_id" => $message_id,
					"text" => 'Cool' 
			) );
		}
	} else {
		
		apiRequestJson ( "sendMessage", array (
				'chat_id' => $chat_id,
				"text" => 'I understand only text messages' 
		) );
	}
}
function startFunction($chat_id, $message_id) {
	$file = fopen ( "start.txt", "r" );
	$response = fread ( $file, filesize ( "start.txt" ) );
	fclose ( $file );
	apiRequestJson ( "sendMessage", array (
			'chat_id' => $chat_id,
			'text' => $response,
			'parse_mode' => 'Markdown' 
	) );
}
function occupationOfTheDay($chat_id, $message_id) {
	$url='https://www7.ceda.polimi.it/spazi/spazi/controller/OccupazioniGiornoEsatto.do?csic=MIA&categoria=tutte&tipologia=tutte&giorno_day=4&giorno_month=4&giorno_year=2016&jaf_giorno_date_format=dd%2FMM%2Fyyyy&evn_visualizza=Visualizza+occupazioni';
	$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36',
			CURLOPT_AUTOREFERER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_CONNECTTIMEOUT => 120,
			CURLOPT_TIMEOUT => 120,
	);
	$ch=curl_init($url);
	curl_setopt_array($ch,$options);
	$content=curl_exec($ch);
	curl_close($ch);
	
	$dom=new DOMDocument();
	$dom->loadHTML($content);
	$my=$dom->getElementById('tableContainer');
	$newdom=new DOMDocument();
	$cloned=$my->cloneNode(TRUE);
	$styleFile=fopen("spazi/table-MOZ.css","r");
	$style=fread($styleFile, filesize("spazi/table-MOZ.css"));
	fclose($styleFile);
	$element=$newdom->createElement('style',$style);
	$newdom->appendChild($element);
	$newdom->appendChild($newdom->importNode($cloned, TRUE));
	$myfile=fopen("occupation.html","w");
	fwrite($myfile,$newdom->saveHTML());
	fclose($myfile);
	$cmdLine='xvfb-run --server-args="-screen 0, 1024x768x24" /var/www/telegrambot/webkit2png.py -o /var/www/telegrambot/occupation.png /var/www/telegrambot/occupation.html';
	shell_exec($cmdLine);
}
?>