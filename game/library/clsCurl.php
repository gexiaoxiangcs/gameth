<?php

class clsCurl
{
	/**
	 *
	 */
	const OPT_URL = CURLOPT_URL;

	/**
	 *
	 */
	const OPT_POST = CURLOPT_POST;

	/**
	 *
	 */
	const OPT_POSTFIELDS = CURLOPT_POSTFIELDS;

	/**
	 *
	 */
	const RES_TYPE_STRING = 0;

	/**
	 *
	 */
	const RES_TYPE_JSON = 1;

	/**
	 *
	 */
	const RES_TYPE_JSON_OBJECT = 2;

	/**
	 *
	 */
	const RES_TYPE_PHP = 3;

	/**
	 *
	 */
	const DEBUG_NONE = 0x0000;

	/**
	 *
	 */
	const DEBUG_ERROR = 0x1000;

	/**
	 *
	 */
	const DEBUG_HTTP_ERROR = 0x0100;

	/**
	 *
	 */
	const DEBUG_PARSE = 0x0010;

	/**
	 *
	 */
	const DEBUG_RESULT = 0x0001;

	/**
	 *
	 */
	const DEBUG_ALL = 0x1111;

	/**
	 *
	 */
	const OPT_RETURNTRANSFER = CURLOPT_RETURNTRANSFER;

	/**
	 *
	 */
	const OPT_NOBODY = CURLOPT_NOBODY;

	/**
	 *
	 */
	const OPT_USERAGENT = CURLOPT_USERAGENT;

	/**
	 *
	 */
	const OPT_TIMEOUT = CURLOPT_TIMEOUT;

	/**
	 *
	 */
	const OPT_CONNECTTIMEOUT = CURLOPT_CONNECTTIMEOUT;

	/**
	 *
	 */
	const OPT_REFERER = CURLOPT_REFERER;

	/**
	 *
	 */
	const OPT_COOKIE = CURLOPT_COOKIE;

	/**
	 *
	 */
	const OPT_HEADER = CURLOPT_HEADER;

	/**
	 *
	 */
	const OPT_HTTPHEADER = CURLOPT_HTTPHEADER;

	/**
	 * @var array
	 */
	private $_options = array();

	/**
	 * @var array
	 */
	private $_optionsDefault = array();

	/**
	 * @var string
	 */
	private $_result = NULL;

	/**
	 * @var int
	 */
	private $_errno = 0;

	/**
	 * @var string
	 */
	private $_error = '';

	/**
	 * @var array|null
	 */
	private $_info = NULL;

	/**
	 * if log the debug logs
	 * 1
	 *
	 * @var int
	 */
	private $_debug = 1;

	/**
	 * @var string
	 */
	private $_debugFile = 'curl_error.log"';

	/**
	 * @param int $debug
	 * @param string $file
	 * @return self
	 */
	static public function instance($debug = self::DEBUG_NONE, $file = '')
	{
		static $instance = NULL;
		if(!($instance instanceof self)){
			$instance = new self($debug, $file);
		}
		$instance->reset();
		return $instance;
	}

	/**
	 * @param int $debug
	 * @param string $file
	 */
	public function __construct($debug = self::DEBUG_NONE, $file = NULL)
	{
		$options = array();
		$options[self::OPT_URL] = NULL;
		$options[self::OPT_POST] = 0;
		//$options[self::OPT_POSTFIELDS] = NULL;
		$options[self::OPT_RETURNTRANSFER] = 1;
		$options[self::OPT_NOBODY] = 0;
		$options[self::OPT_USERAGENT] = NULL;
		$options[self::OPT_TIMEOUT] = 10;
		$options[self::OPT_CONNECTTIMEOUT] = 5;
		$options[self::OPT_REFERER] = $_SERVER['REQUEST_URI'];
		$options[self::OPT_COOKIE] = NULL;
		$options[self::OPT_HEADER] = FALSE;
		$options[self::OPT_HTTPHEADER] = array();

		$this->_optionsDefault = $options;

		$this->reset();

		$this->setDebug($debug, $file);
	}

	/**
	 *
	 */
	public function __destruct()
	{
	}

	/**
	 * @return int
	 */
	public function exec()
	{
		$curl = curl_init();
		curl_setopt_array($curl, $this->_options);
		$this->_result = curl_exec($curl);
		$this->_info = curl_getinfo($curl);
		$errno = curl_errno($curl);
		if($errno > 0){
			$this->_setError($errno, curl_error($curl));
		} else{
			$code = $this->code();
			if($code != 200){
				$this->_setError($code, $this->_result);
			} else{
				$this->_setError(0, '');
			}
		}
		curl_close($curl);
		return $this->error();
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setOption($name, $value)
	{
		$this->_options[$name] = $value;
	}

	/**
	 * @param array $data
	 * @return void
	 */
	public function setOptions($data)
	{
		foreach($data as $name => $value){
			$this->setOption($name, $value);
		}
	}

	/**
	 * @param null $type
	 * @return mixed|null
	 */
	public function result($type = NULL)
	{
		if($this->_errno > 0){
			return FALSE;
		}
		$decoded = 1;
		if($type == self::RES_TYPE_JSON){
			$result = @json_decode($this->_result, TRUE);
		} elseif($type == self::RES_TYPE_PHP){
			$result = @unserialize($this->_result);
		} elseif($type == self::RES_TYPE_JSON_OBJECT){
			$result = @json_decode($this->_result, FALSE);
		} else{
			$decoded = 0;
			$result = $this->_result;
		}

		if($decoded && $result === FALSE){
			return $this->_setError(9999, 'Data Parse Error.');
		}
		return $result;
	}

	/**
	 * @return string
	 */
	public function error()
	{
		return $this->_error;
	}

	/**
	 * @return int
	 */
	public function errno()
	{
		return $this->_errno;
	}

	/**
	 * @return array
	 */
	public function info()
	{
		return $this->_info;
	}

	/**
	 * @return void
	 */
	public function reset()
	{
		$this->_setError();
		$this->_info = NULL;
		$this->_result = NULL;
		$this->_options = $this->_optionsDefault;
	}

	/**
	 * @return int
	 */
	public function code()
	{
		return isset($this->_info['http_code']) ? $this->_info['http_code'] : 0;
	}

	/**
	 * @param int $errno
	 * @param string $error
	 * @return int
	 */
	private function _setError($errno = 0, $error = '')
	{
		$this->_errno = $errno;
		$this->_error = $error;
		if($errno > 0){
			$this->_result = NULL;
		}

		return FALSE;
	}

	/**
	 * @param int $debug
	 * @param string $file
	 */
	public function setDebug($debug = 0, $file = NULL)
	{
		$this->_debug = $debug;
		if(!is_null($file)){
			$this->_debugFile = $file;
		}
	}
}
