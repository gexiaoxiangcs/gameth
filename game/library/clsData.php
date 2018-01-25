<?php

if(!defined('JSON_UNESCAPED_UNICODE')){
	define('JSON_UNESCAPED_UNICODE', 9999);
}
define('JSON_UNESCAPED_SUPPORT', JSON_UNESCAPED_UNICODE != 9999);

class clsData
{
	/**
	 * @param mixed $data
	 * @param int $option
	 * @return string
	 */
	static public function jsonEncode($data, $option = 0)
	{
		if(JSON_UNESCAPED_SUPPORT){
			return json_encode($data, $option);
		}
		$data = json_encode($data);
		if($option == JSON_UNESCAPED_UNICODE && strpos($data, '\u') !== FALSE){
			//$data = preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $data);
		}
		return $data;
	}

	/**
	 * @param string $json
	 * @param bool $assoc
	 * @param int $depth
	 * @param int $options
	 * @return mixed
	 */
	static public function jsonDecode($json, $assoc = FALSE, $depth = 512, $options = 0)
	{
		return json_decode($json, $assoc, $depth, $options);
	}
}
