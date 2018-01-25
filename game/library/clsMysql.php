<?php

class clsMysql
{
	/**
	 * Enter description here ...
	 * @var resource
	 */
	private $_link = NULL;

	/**
	 * Enter description here ...
	 * @var int
	 */
	private $_debug = 0;

	/**
	 * Enter description here ...
	 * @var int
	 */
	private $_count = 0;

	/**
	 * Enter description here ...
	 * @var string
	 */
	private $_last = '';

	/**
	 * Enter description here ...
	 * @var string
	 */
	private $_charset = 'utf8';

	/**
	 * Enter description here ...
	 * @var array
	 */
	private $_sqlArray = array();

	/**
	 * @var int  , ms
	 */
	private $_slowTimer = 50;

	/**
	 * @param string $charset
	 * @param bool $debug
	 */
	public function __construct($charset = 'utf8', $debug = FALSE)
	{
		$this->_charset = $charset;
		$this->_debug = $debug || TRUE;
	}

	/**
	 *
	 */
	public function __destruct()
	{
		$this->close();
	}

	/**
	 * @return string
	 */
	public function version()
	{
		return mysql_get_server_info($this->_link);
	}

	/**
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $name
	 * @return bool
	 */
	public function connect($host, $user, $pass, $name = '')
	{
		$this->_link = mysql_connect($host, $user, $pass);
		if(empty($this->_link)){
			return $this->_halt("Connect to database server(#{$host}#{$user}) failed!");
		}
		if($name){
			$this->dbname($name);
		}
		$this->charset($this->_charset);
		return TRUE;
	}

	/**
	 * Enter description here ...
	 * @param string $name
	 * @return bool
	 */
	public function dbname($name)
	{
		return mysql_select_db($name, $this->_link);
	}

	/**
	 * @param string $charset
	 */
	public function charset($charset = NULL)
	{
		if($charset){
			$this->query('set names ' . $charset);
		}
	}

	/**
	 * Enter description here ...
	 * @param resource $query
	 * @param int $type
	 * @return array|bool
	 */
	public function fetchArray($query, $type = MYSQL_ASSOC)
	{
		if(!is_resource($query)){
			return FALSE;
		}
		return mysql_fetch_array($query, $type);
	}

	/**
	 * Enter description here ...
	 * @param string $sql
	 * @param bool $silent
	 * @return resource
	 */
	public function query($sql, $silent = FALSE)
	{
		if(!$this->_link){
			return $this->_halt('No database connection alive!');
		}
		$timer = microtime(1);
		if($silent){
			$query = mysql_unbuffered_query($sql, $this->_link);
		} else{
			$query = mysql_query($sql, $this->_link);
		}
		$timer = 1000 * (microtime(1) - $timer);
		$this->_debug($sql, $query, $timer);
		$this->_last = $sql;
		$this->_sqlArray[] = $sql;
		$this->_count++;
		return $query;
	}

	/**
	 * Enter description here ...
	 * @return int
	 */
	public function insertId()
	{
		if(!$this->_link){
			return $this->_halt('[insertId]No database connection alive!');
		}
		$id = mysql_insert_id($this->_link);
		if($id <= 0){
			$id = $this->result($this->query("SELECT last_insert_id()"), 0);
		}
		return $id;
	}

	/**
	 * Enter description here ...
	 * @return int
	 */
	public function affected()
	{
		return $this->_link ? mysql_affected_rows($this->_link) : $this->_halt('[affected]No database connection alive!');
	}

	/**
	 * Enter description here ...
	 * @return string
	 */
	public function error()
	{
		return $this->_link ? mysql_error($this->_link) : mysql_error();
	}

	/**
	 * Enter description here ...
	 * @return int
	 */
	public function errno()
	{
		return $this->_link ? mysql_errno($this->_link) : mysql_errno();
	}

	/**
	 * Enter description here ...
	 * @param resource $query
	 * @param int $row
	 * @param int|string $field
	 * @return string
	 */
	public function result($query, $row = 0, $field = 0)
	{
		if(!is_resource($query)){
			return FALSE;
		}
		$query = mysql_result($query, $row, $field);
		return $query;
	}

	/**
	 * @param resource $query
	 * @return bool|int
	 */
	public function rows($query)
	{
		if(!is_resource($query)){
			return FALSE;
		}
		$query = mysql_num_rows($query);
		return $query;
	}

	/**
	 * @param resource $query
	 * @return bool|int
	 */
	public function fieldsNumber($query)
	{
		if(!is_resource($query)){
			return FALSE;
		}
		return mysql_num_fields($query);
	}

	/**
	 * @param resource $query
	 * @return bool
	 */
	public function free($query)
	{
		if(!is_resource($query)){
			return FALSE;
		}
		return mysql_free_result($query);
	}

	/**
	 * @param resource $query
	 * @param int $type
	 * @return array|bool
	 */
	public function next($query, $type = MYSQL_ASSOC)
	{
		if(!is_resource($query)){
			return FALSE;
		}
		return mysql_fetch_array($query, $type);
	}

	/**
	 * @param resource $query
	 * @return bool|object
	 */
	public function fields($query)
	{
		if(!is_resource($query)){
			return FALSE;
		}
		return mysql_fetch_field($query);
	}

	/**
	 * @return bool
	 */
	public function close()
	{
		if($this->_link){
			@mysql_close($this->_link);
		}
		$this->_link = NULL;
		return FALSE;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	protected function __slashes($string)
	{
		return addslashes(stripslashes($string));
	}

	/**
	 * @param string $sql
	 * @param float $timer
	 * @return bool
	 */
	private function _logSlowQuery($sql, $timer)
	{
		$message = sprintf("%10.3f\t%s", $timer, $sql);
		return clsLog::write("mysql_query_error", $message);
	}

	/**
	 * @param string $message
	 * @return bool
	 */
	private function _halt($message = '')
	{
		$message = sprintf("%s - %s\n\t%s\n\t%s\n", $this->errno(), $this->error(), $_SERVER['REQUEST_URI'], $message);
		return clsLog::write("mysql_query_error", $message);
	}

	/**
	 * @param string $sql
	 * @param resource|bool $result
	 * @param float $timer
	 * @return bool
	 */
	private function _debug($sql, &$result, $timer)
	{
		if(defined('SYS_ENVIRONMENT') && SYS_ENVIRONMENT != 'PRO'){
			$log = sprintf("%2d%10.3f\t%s", $result ? 1 : 0, $timer, $sql);
			clsLog::synWrite('mysql_query_list', $log);
		}
		if(!$result){
			return $this->_halt('Query Error : ' . $sql);
		}

		if($timer >= $this->_slowTimer){
			return $this->_logSlowQuery($sql, $timer);
		}
		return TRUE;
	}
}
