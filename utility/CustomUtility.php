<?php

class CustomUtility
{
	public static function convertArrayToEntityObject($object,$arr)
	{
		foreach ($arr as $key => $value) {
            $object->{$key} = $value;
        }
        return $object;
	}
}
?>