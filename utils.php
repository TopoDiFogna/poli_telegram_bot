<?php
function getLastDate(){
	$myfile=fopen("lastDate.txt","r");
	$lastDate=fread($myfile,filesize($myfile));
	fclose($myfile);
	return $lastDate;
}
?>