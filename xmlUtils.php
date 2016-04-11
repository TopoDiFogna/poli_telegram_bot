<?php

/*This Function return the ID of a given Classroom.
 * paramaters: String contain the name of the class
 * Return: if the class exists returns the corresponding id,
 * 		   -1 otherwise.
 */
function idOfGivenClassroom($className) {
	if (is_string ( $className )) {
		$xmldoc = new DOMDocument ();
		$xmldoc->load ( "polimiClassroom.xml" );
		$xml = new Domxpath ( $xmldoc );
		$items = $xml->query ( "//classroom[name='$className']/id" );
		if ($items->length == 1) {
			$returnValue=$items->item ( 0 )->nodeValue;
			$returnValue=rtrim($returnValue, "\n");
			return $returnValue;
		} else {
			return - 1;
		}
	}else{
		return -1;
	}
}
?>