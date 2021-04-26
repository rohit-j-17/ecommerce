<?php

class DateTimeHelper
{
	public static function getCurrentDatetime(){
		return date("Y-m-d H:i:s");
	}
	
	public static function addDaysInDatetime($dtStamp,$days){
		return date('Y-m-d H:i:s', strtotime($dtStamp. ' + '.$days.' days'));
	}
}
?>