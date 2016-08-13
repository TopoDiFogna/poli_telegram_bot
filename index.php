<?php
include_once 'network.php';
include_once 'bot.php';
include_once 'xmlUtils.php';
include_once 'freeUtils.php';
include_once 'objectFree.php';

echo "<html><head></head><body><h2>Politecnico di Milano Telegram Bot FrontEnd</h2></body></html>";

//reads the data from the request
$content = file_get_contents("php://input");

//decode the json request
$result = json_decode($content, true);

//if the request is empty just exit
if (!count($result)) {
	exit;
}
else {//otherwise process the message
	processMessage($result["message"]);
}
?>