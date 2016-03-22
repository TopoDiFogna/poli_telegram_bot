<?php
include_once 'network.php';
include_once 'bot.php';

$offset=0;
$content = file_get_contents(API_URL. "getUpdates?offset=".$offset);
$update = json_decode($content, true);
$result=$update["result"];
if (!count($result)) {
	exit;
}
$firstMessage=$result[0];
$offset=$firstMessage["update_id"]+1;
$content = file_get_contents(API_URL. "getUpdates?offset=".$offset);

if (!$firstMessage) {
	echo("error");
	exit;
}

if (isset($firstMessage["message"])) {
	processMessage($firstMessage["message"]);
}
?>