<?php
include_once 'network.php';
include_once 'bot.php';

//read data from the request
$content = file_get_content("php://input");
$update = json_decode($content, true); //decode the json request
$result=$update["result"]; //get the request containing the update

//if the request is empty just exit
if (!count($result)) {
	exit;
}
else {//therwise process the message
	processMessage($result["message"]);
}
?>