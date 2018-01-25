<?php

class clsCurlInstance
{
	/**
	 * @var int
	 */
	static private $_errno = 0;

	/**
	 * @var string
	 */
	static private $_error = '';

	/**
	 * @param string $url
	 * @param string $type
	 * @param array $options
	 * @return mixed|null
	 */
	static public function get($url, $type = '', $options = NULL)
	{
		if(!is_array($options)){
			$options = array();
		}
		$options[clsCurl::OPT_POST] = 0;
		//$options[clsCurl::OPT_POSTFIELDS] = NULL;
		return self::_send($url, $type, $options);
	}

	/**
	 * @param string $url
	 * @param null $data
	 * @param string $type
	 * @param array $options
	 * @return mixed|null
	 */
	static public function post($url, $data = NULL, $type = '', $options = NULL)
	{
		if(!is_array($options)){
			$options = array();
		}
		$options[clsCurl::OPT_POST] = 1;
		$options[clsCurl::OPT_POSTFIELDS] = $data;
		return self::_send($url, $type, $options);
	}

	/**
	 * @return int
	 */
	static public function errno()
	{
		return self::$_errno;
	}

	/**
	 * @return string
	 */
	static public function error()
	{
		return self::$_error;
	}

	/**
	 * @param string $url
	 * @param int $type
	 * @param array $options
	 * @return mixed|null
	 */
	static private function _send($url, $type, $options)
	{
		$options[clsCurl::OPT_URL] = $url;

		$curl = clsCurl::instance();
		$curl->setOptions($options);
		$curl->exec();
		$result = $curl->result($type);
		self::_setError($curl->errno(), $curl->error());
		$curl = NULL;
		unset($curl);
		return $result;
	}

	/**
	 * @param int $errno
	 * @param string $error
	 */
	static private function _setError($errno, $error)
	{
		self::$_errno = $errno;
		self::$_error = $error;
	}
}
