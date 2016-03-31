<?php
function processMessage($message) {
	// process incoming message
	$message_id = $message ['message_id'];//used in replies
	
	$chat_id = $message ['chat'] ['id'];//chat to send the message to
	
	$text_message = $message['text'];//what has been sent to the bot
	
	if (isset ( $text_message )) {
		
		$text = $message ['text']; //incoming text message
		
		if (strpos ( $text, "/start" ) === 0) {
			
			apiRequestJson ( "sendMessage", array (
					'chat_id' => $chat_id,
					"text" => 'Hello',
					'reply_markup' => array (
							
							'keyboard' => array (
									array (
											'Hello',
											'Hi' 
									) 
							),
							
							'one_time_keyboard' => true,
							
							'resize_keyboard' => true 
					) 
			) );
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