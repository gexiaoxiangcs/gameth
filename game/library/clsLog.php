<?php

class clsLog
{
	/**
	 * @var string
	 */
	//static private $_dir = '/tmp/';
	static private $_dir = SYS_LOG_DIR;

	/**
	 * @var array
	 */
	static private $_data = array();

	/**
	 * @param string $dir
	 * @return bool
	 */
	static public function set($dir)
	{
		$dir = realpath($dir);
		if(!is_writable($dir)){
			return FALSE;
		}
		$info = explode(DIRECTORY_SEPARATOR, $dir);
		if(preg_match('/(etc|boot)/is', $info[1])){
			return FALSE;
		}
		if(!is_dir($dir)){
			return FALSE;
		}
		self::$_dir = $dir;
		return TRUE;
	}

	/**
	 * @param string $name
	 * @param string $message
	 * @param string $split
	 * @return bool
	 */
	static public function write($name, $message, $split = "\n")
	{
		return self::_instance()->_write($name, $message, $split);
	}

	/**
	 * @param string $name
	 * @param string $message
	 * @param string $split
	 * @return bool
	 */
	static public function synWrite($name, $message, $split = "\n")
	{
		return self::_instance()->_synWrite($name, $message, $split);
	}

	/**
	 * @return clsLog
	 */
	static private function _instance()
	{
		static $_instance = NULL;
		if(empty($_instance) || !($_instance instanceof self)){
			$_instance = new self;
		}
		return $_instance;
	}

	public function __construct()
	{
	}

	public function __destruct()
	{
		foreach(self::$_data as $file => $message){
			$message = implode("", $message);
			file_put_contents($file, $message, FILE_APPEND);
		}
	}

	/**
	 * @param string $name
	 * @param string $message
	 * @param string $split
	 * @return bool
	 */
	private function _write($name, $message, $split = "\n")
	{
		list($file, $text) = $this->_filter($name, $message, $split);
		return file_put_contents($file, $text, FILE_APPEND);
	}


	/**
	 * @param string $name
	 * @param string $message
	 * @param string $split
	 * @return bool
	 */
	private function _synWrite($name, $message, $split = "\n")
	{
		list($file, $text) = $this->_filter($name, $message, $split);
		if(empty(self::$_data[$file])){
			self::$_data[$file] = array();
		}
		self::$_data[$file][] = $text;
		return TRUE;
	}

	/**
	 * @param $name
	 * @param $message
	 * @param $split
	 * @return array
	 */
	private function _filter($name, $message, $split)
	{
		if($split){
			$message .= $split;
		}
		$message = "[" . date("Y-m-d H:i:s") . "]\t" . $message;
		$name = preg_replace('/[^\w\.\_\-]+/is', '', $name);
		$file = self::$_dir . "{$name}_" . date("Y-m-d") . ".log";
		return array($file, $message);
	}
}
