<?php
function getLastDate() {
	$myfile = fopen ( "lastDate.txt", "r" );
	$lastDate = fread ( $myfile, filesize ( "lastDate.txt" ) );
	fclose ( $myfile );
	return $lastDate;
}
function getLastTime() {
	$myfile = fopen ( "lastTime.txt", "r" );
	$lastDate = fread ( $myfile, filesize ( "lastTime.txt" ) );
	fclose ( $myfile );
	return $lastDate;
}
function updateDateAndTime() {
	$newDate = date ( 'j-n-Y' );
	$myfile = fopen ( "lastDate.txt", "w" );
	fwrite ( $myfile, $newDate );
	fclose ( $myfile );
	$time = date ( 'H', time () );
	$myfile = fopen ( "lastTime.txt", "w" );
	fwrite ( $myfile, $time );
	fclose ( $myfile );
}
function timeIsWell() {
	$day = date ( 'j' );
	$month = date ( 'n' );
	$year = date ( 'Y' );
	$time = date ( 'H', time () );
	$lastDate = getLastDate ();
	$splittedDate = explode ( '-', $lastDate );
	$lastTime = getLastTime ();
	if ($year > $splittedDate [2]) {
		return true;
	} else if ($month > $splittedDate [1]) {
		return true;
	} else if ($day > $splittedDate [0]) {
		return true;
	} else if ($time >= $lastTime + 2) {
		return true;
	} else {
		return false;
	}
}
?>