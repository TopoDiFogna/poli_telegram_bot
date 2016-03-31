<?php
function processMessage($message) {
	// process incoming message
	$message_id = $message ['message_id'];//used in replies
	
	$chat_id = $message ['chat'] ['id'];//chat to send the message to
	
	$text_message = $message['text'];//what has been sent to the bot
	
	if (isset ( $text_message )) {
		
		$text = $message ['text']; //incoming text message
		
		if (strpos ( $text, "/start" ) === 0) {
			
			$response='*ciao*
_cacca_';
			
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