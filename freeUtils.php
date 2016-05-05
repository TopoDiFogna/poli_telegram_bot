<?php
/**
 * This Function return the Array of all saved Object.
 * If there aren't any save object returns a empty array.
 *
 * @param the filePath of the file
 * @return array
 */
function retriveObject($filePath) {
	$myfile = fopen ( $filePath, "r" );
	if (filesize ( $filePath ) == 0) {
		return array ();
	} else {
		$allObj = fread ( $myfile, filesize ( $filePath ) );
		$objArraySer = explode ( "$$$", $allObj );
		$objArray = array ();
		error_log($objArraySer);
		foreach ( $objArraySer as $objSer ) {
			$obj = unserialize ( $objSer );
			array_push ( $objArray, $obj );
		}
		return $objArray;
	}
}

/**
 * This method returns array to send as Keyboard read from the file given as input.
 *
 * @param string $filePath        	
 */
function getArrayForKeyboard($filePath) {
	$myfile = fopen ( $filePath, "r" );
	$readString = fread ( $myfile, filesize ( $filePath ) );
	fclose($myfile);
	$arrayString = explode ( " ", $readString );
	return $arrayString;
}

/**
 * This method serializes and saves the array passed as parameters, in to the file passed as input
 *
 * @param array $ObjArray        	
 * @param string $fileDestinationPath        	
 */
function serializeObject($ObjArray, $fileDestinationPath) {
	$string = "";
	foreach ( $ObjArray as $obj ) {
		$serialized = serialize ( $obj );
		$string=$string.$serialized."$$$";
	}
	$myfile=fopen($fileDestinationPath, "w");
	fwrite($myfile, $string);
	fclose($myfile);
}
?>