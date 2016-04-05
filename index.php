<?php
include_once 'network.php';
include_once 'bot.php';
include_once 'utils.php';

//read data from the request
$content = file_get_contents("php://input");
$result = json_decode($content, true); //decode the json request

//if the request is empty just exit
if (!count($result)) {
	exit;
}
else {//therwise process the message
	processMessage($result["message"]);
}
?>