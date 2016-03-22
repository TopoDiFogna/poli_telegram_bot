<?php
function processMessage($message) {
	// process incoming message
	$message_id = $message ['message_id'];
	
	$chat_id = $message ['chat'] ['id'];
	
	if (isset ( $message ['text'] )) {
		
		// incoming text message
		
		$text = $message ['text'];
		
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
			
			apiRequestWebhook ( "sendMessage", array (
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