<?php
function processMessage($message) {
	// process incoming message
	$message_id = $message ['message_id'];//used in replies
	
	$chat_id = $message ['chat'] ['id'];//chat to send the message to
	
	switch ($message){ 
		case array_key_exists('text', $message): 
			$text_message = $message['text'];
			processTextMessage($text_message,$chat_id,$message_id);
			break;
		case array_key_exists('audio', $message):
			break;
		case array_key_exists('document', $message):
			break;
		case array_key_exists('sticker', $message):
			break;
		case array_key_exists('video', $message):
			break;
		case array_key_exists('voice', $message):
			break;
		case array_key_exists('contact', $message):
			break;
		case array_key_exists('location', $message):
			break;
		default:
			break;
	}
}

function processTextMessage($text,$chat_id,$message_id){
	if (isset ( $text)) {
	
		if (strpos ( $text, "/start" ) === 0) {
				
			$file=fopen("start.txt", "r");
			$response=fread($file, filesize("start.txt"));
			fclose($file);
			apiRequestJson ( "sendMessage", array (
					'chat_id' => $chat_id,
					'text' => $response,
					'parse_mode' => 'Markdown',
			)
					);
		} else if ($text === "Hello" || $text === "Hi") {
				
			apiRequestJson ( "sendMessage", array (
					'chat_id' => $chat_id,
					"text" => 'Nice to meet you'
			) );
		} else if (strpos ( $text, "/stop" ) === 0) {
				
			// stop now
		} else {
				
			apiRequestJson( "sendMessage", array (
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
?>