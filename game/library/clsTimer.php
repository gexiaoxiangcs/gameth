<?php

class clsTimer
{
	/**
	 * @var array
	 */
	private $_timer = array();

	/**
	 * @var float
	 */
	private $_start = 0;

	/**
	 * @var int
	 */
	private $_type = 0;

	/**
	 * @var string
	 */
	private $_file = '/tmp/timer.log';

	/**
	 * @var float
	 */
	private $_lower = 0;

	/**
	 * @var float
	 */
	private $_upper = 999999999;

	/**
	 * @var int
	 */
	private $_all = 0;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_start = microtime(1);
		$this->_type = isset($_COOKIE['__D__']) ? intval($_COOKIE['__D__']) : 0;
		if(isset($_REQUEST['_AJAX_'])){
			$this->_type = 0;
		}
	}

	/**
	 *
	 */
	public function __destruct()
	{
		$this->_out();
	}

	/**
	 * @return self
	 */
	static public function instance()
	{
		static $instance = NULL;
		if(!($instance instanceof self)){
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * @param string $msg
	 */
	static public function add($msg = '')
	{
		self::instance()->_add($msg);
	}

	/**
	 * @param string|null $file
	 * @param int $type
	 * @param float $lower
	 * @param float $upper
	 */
	static public function init($file = NULL, $type = 0, $lower = -1, $upper = -1)
	{
		self::instance()->_init($file, $type, $lower, $upper);
	}

	/**
	 * @param int $type
	 * @param bool $cond
	 */
	static public function setType($type = 0, $cond = FALSE)
	{
		if($cond){
			self::instance()->_setType($type);
		}
	}

	/**
	 * @param int $type
	 */
	private function _setType($type)
	{
		$this->_type = $type;
	}

	/**
	 * @param null $file
	 * @param int $type
	 * @param float $lower
	 * @param float $upper
	 */
	private function _init($file = NULL, $type = 0, $lower = -1, $upper = -1)
	{
		if($file){
			$this->_file = $file;
		}
		if($type){
			$force = 0;
			if($type < 0){
				$force = 1;
				$type = abs($type);
			}
			if($type > 0 || $force){
				$this->_type = $type;
			}
		}
		if($lower >= 0){
			$this->_lower = $lower;
		}
		if($upper >= 0){
			$this->_upper = $upper;
		}
	}

	/**
	 * @param string $msg
	 */
	private function _add($msg = '')
	{
		$timer = microtime(1);
		$this->_all = $timer - $this->_start;
		$this->_timer[] = array($timer, $msg);
	}

	/**
	 *
	 */
	private function _out()
	{
		$this->_add('Page End.');
		$this->_all = sprintf("%5.3f", $this->_all * 1000);
		if($this->_type == 0 || $this->_all > $this->_upper || $this->_all < $this->_lower){
			return;
		}

		$res = "";
		$last = $this->_start;
		foreach($this->_timer as $k => $t){
			$tm1 = sprintf("%5.3f", ($t[0] - $this->_start) * 1000);
			$tm2 = sprintf("%5.3f", ($t[0] - $last) * 1000);
			$res .= sprintf("[%03s] %10s %10s --- %s\n", $k, $tm1, $tm2, $t[1]);
			$last = $t[0];
		}
		$res .= sprintf("----------\n[ALL] %10s %10s --- All Time\n", $this->_all, $this->_all);
		if($this->_type == 3){
			echo $res, "\n\n";
		} elseif($this->_type == 1){
			echo '<div style="position:fixed;top:100px;left:10px;border:1px solid #ABCDEF;padding:10px;background:#FFFFFF;"><pre>', $res, '</pre></div>';
		} else{
			$res = $_SERVER['REQUEST_URI'] . "\n" . $res;
			$res = "-----------------\n" . $res;
			file_put_contents($this->_file, $res, FILE_APPEND);
		}
	}
}
    