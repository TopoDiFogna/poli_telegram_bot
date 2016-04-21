<?php
class freeObj{
	
	public $message_id;
	public $chat_id;
	public $startTime;
	public $endTime;
	public $day;
	
	public function __construct($properties){
		if(is_array($properties)){
			if(isset($properties["message_id"])){
				$this->message_id=$properties["message_id"];
			}
			if(isset($properties["chat_id"])){
				$this->chat_id=$properties["chat_id"];
			}
			if(isset($properties["startTime"])){
				$this->startTime=$properties["startTime"];
			}
			if(isset($properties["endTime"])){
				$this->endTime=$properties["endTime"];
			}
			if(isset($properties["day"])){
				$this->day=$properties["day"];
			}
		}
	}
	
	public function addProperty($newProperty){
		if(isset($this->startTime)){
			if(isset($this->endTime)){
				if(!isset($this->day)){
					$this->day=$newProperty;
					return true;
				}
			}else{
				$this->endTime=$newProperty;
				return "day";
			}
		}else{
			$this->startTime=$newProperty;
			return "endTime";
		}
	}
	
	public function setMessage_id($new_id){
		$this->message_id=$new_id;
	}
	
	public function getMessage_id($new_id){
		if(isset($this->message_id)){
			$this->message_id=$new_id;
		}
		else{
			return false;
		}
	}
	
	public function executeCommandFree(){
		return classFree($this->chat_id, $this->startTime, $this->endTime, $this->day);
	}
}
?>