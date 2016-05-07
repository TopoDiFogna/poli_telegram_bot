<?php
class freeObj {
	public $message_id;
	public $chat_id;
	public $startTimeH;
	public $startTimeM;
	public $endTimeH;
	public $endTimeM;
	public $day;
	public function __construct($properties) {
		if (is_array ( $properties )) {
			if (isset ( $properties ["message_id"] )) {
				$this->message_id = $properties ["message_id"];
			}
			if (isset ( $properties ["chat_id"] )) {
				$this->chat_id = $properties ["chat_id"];
			}
			if (isset ( $properties ["startTimeH"] )) {
				$this->startTimeH = $properties ["startTimeH"];
			}
			if (isset ( $properties ["startTimeM"] )) {
				$this->startTimeH = $properties ["startTimeM"];
			}
			if (isset ( $properties ["endTimeH"] )) {
				$this->endTimeH = $properties ["endTimeH"];
			}
			if (isset ( $properties ["endTimeM"] )) {
				$this->endTimeH = $properties ["endTimeM"];
			}
			if (isset ( $properties ["day"] )) {
				$this->day = $properties ["day"];
			}
		}
	}
	public function addProperty($newProperty) {
		if (isset ( $this->startTimeH )) {
			if (isset ( $this->startTimeM )) {
				if (isset ( $this->endTimeH )) {
					if (isset ( $this->endTimeM )) {
						if (! isset ( $this->day )) {
							if ($newProperty == "Today") {
								$this->day = date ( "j" ) . "-" . date ( "n" ) . "-" . date ( "Y" );
							} else {
								$datetime = new DateTime ( date ( "j" ) . "-" . date ( "n" ) . "-" . date ( "Y" ) );
								$datetime->modify ( '+1 day' );
								$this->day = $datetime->format("d-m-Y");
							}
							return true;
						}
					} else {
						$this->endTimeM = $newProperty;
						return "Selected day";
					}
				} else {
					$this->endTimeH = $newProperty;
					return "EndTime minutes";
				}
			} else {
				$this->startTimeM = $newProperty;
				return "EndTime hours";
			}
		} else {
			$this->startTimeH = $newProperty;
			return "StartTime minutes";
		}
	}
	public function setMessage_id($new_id) {
		$this->message_id = $new_id;
	}
	public function getMessage_id() {
		if (isset ( $this->message_id )) {
			return $this->message_id;
		} else {
			return false;
		}
	}
	public function getChat_id() {
		if (isset ( $this->chat_id )) {
			return $this->chat_id;
		} else {
			return false;
		}
	}
	public function executeCommandFree() {
		$deltahour = intval($this->endTimeH) - intval($this->starTimeH);
		error_log($this->endTimeH." ".$this->startTimeH);
		error_log(intval($this->endTimeH)." ".intval($this->starTimeH));
		error_log("--------------------------".$deltahour);
		return false;
		if ($deltahour > 0) {
			classFree ( $this->chat_id, $this->startTimeH . ":" . $this->startTimeM, $this->endTimeH . ":" . $this->endTimeM, $this->day );
			return true;
		} elseif ($deltahour == 0) {
			$deltaMinutes = intval($this->endTimeM,10) - intval($this->startTimeM,10);
			if ($deltaMinutes >= 0) {
				classFree ( $this->chat_id, $this->startTimeH . ":" . $this->startTimeM, $this->endTimeH . ":" . $this->endTimeM, $this->day );
				return true;
			}
			else{
				return false;
			}
		}
		return false;
	}
}
?>