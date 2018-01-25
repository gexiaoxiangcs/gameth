<?php

class clsDatabase extends clsMysql
{
	public function __construct($debug = FALSE)
	{
		parent::__construct($debug);
	}

	/**
	 * 执行INSERT操作
	 *
	 * @param string $table
	 * @param array $data
	 * @param boolean $returnId
	 * @param string $onDuplicate
	 * @return int
	 */
	public function insert($table, $data = array(), $returnId = TRUE, $onDuplicate = '')
	{
		return $this->_insert($table, $data, FALSE, $returnId, $onDuplicate);
	}

	/**
	 * 执行 REPLACE 操作
	 *
	 * @param string $table
	 * @param array $data
	 * @return int
	 */
	public function replace($table, $data = array())
	{
		return $this->_insert($table, $data, TRUE);
	}


	/**
	 * 更新表数据
	 * @param string $table
	 * @param array $data
	 * @param string $cond
	 * @param bool $affected
	 * @param int $limit
	 * @return int
	 */
	public function update($table, $data = array(), $cond = '', $affected = FALSE, $limit = 0)
	{
		if(empty($data)){
			return -1;
		}
		$values = array();
		if(is_array($data)){
			foreach($data as $k => $v){
				if(is_int($k)){
					$values[] = $v;
				} else{
					$values[] = "`" . $this->slashes($k) . "` = '" . $this->slashes($v) . "'";
				}
			}
			$data = implode(",", $values);
		}
		$sql = "update {$table} set {$data}";
		if($cond){
			$sql .= " where {$cond}";
		}
		if($limit > 0){
			$sql .= " limit " . $limit;
		}
		$res = $this->query($sql);
		if(!$res){
			return -1;
		}
		if($affected){
			return $this->affected();
		}
		return 0;
	}

	/**
	 * Enter description here ...
	 * @param string $table
	 * @param string $cond
	 * @param int $limit
	 * @return int
	 */
	public function delete($table, $cond = '', $limit = 0)
	{
		$sql = "delete from {$table}";
		if($cond){
			$sql .= " where {$cond}";
		}
		if($limit){
			$sql .= " limit {$limit}";
		}
		$res = $this->query($sql);
		return $res ? 0 : -1;
	}

	/**
	 * Enter description here ...
	 * @param string $table
	 * @param string $field
	 * @param string $cond
	 * @param string $order
	 * @return array|null
	 */
	public function select($table, $field = '*', $cond = '', $order = '')
	{
		if(empty($field)){
			$field = $field = '*';
		}
		$sql = "select {$field} from {$table}";
		if($cond){
			$sql .= " where {$cond} ";
		}
		if($order){
			$sql .= " order by {$order} ";
		}
		$sql .= " limit 1";
		$q = $this->query($sql);
		$rs = $this->fetchArray($q);
		return $rs ? $rs : NULL;
	}

	/**
	 * Enter description here ...
	 * @param string $table
	 * @param string $cond
	 * @return int
	 */
	public function count($table, $cond = '')
	{
		$sql = "select count(1) as c from {$table}";
		if($cond){
			$sql .= " where {$cond} ";
		}
		$q = $this->query($sql);
		$rs = $this->fetchArray($q);
		return $rs ? intval($rs['c']) : 0;
	}

	/**
	 * Enter description here ...
	 * @param string $table
	 * @param string $fields
	 * @param string $cond
	 * @param string $order
	 * @param int $limit
	 * @param int $start
	 * @param string $primaryKey
	 * @return array
	 */
	public function fetch($table, $fields = '*', $cond = '', $order = '', $limit = 0, $start = 0, $primaryKey = '')
	{
		if(empty($fields)){
			$fields = '*';
		}
		$sql = "select {$fields} from {$table}";
		if($cond){
			$sql .= " where {$cond} ";
		}
		if($order){
			$sql .= " order by {$order}";
		}
		if($start > 0 || $limit > 0){
			$sql .= " limit ";
			if($start > 0){
				$sql .= $start . ",";
			}
			if($limit > 0){
				$sql .= $limit;
			}
		}
		$q = $this->query($sql);
		$data = array();
		while(($rs = $this->fetchArray($q)) != FALSE){
			if($primaryKey && isset($rs[$primaryKey])){
				$data[$rs[$primaryKey]] = $rs;
			} else{
				$data[] = $rs;
			}
		}
		return $data;
	}

	/**
	 * 转义非法字符
	 * @param string $str
	 * @return string
	 */
	public function slashes($str)
	{
		return addslashes(stripslashes($str));
	}

	/**
	 * Enter description here ...
	 * @param string $table
	 * @param string $field
	 * @param string $cond
	 * @param string $order
	 * @return string
	 */
	public function one($table, $field, $cond = '', $order = '')
	{
		$data = $this->select($table, $field, $cond, $order);
		if(is_array($data)){
			foreach($data as $v){
				return $v;
				break;
			}
		}
		return NULL;
	}

	/**
	 * 执行 INSERT/REPLACE 操作
	 *
	 * @param string $table
	 * @param array $data
	 * @param boolean $replace
	 * @param boolean $returnId
	 * @param string $onDuplicate
	 * @return int
	 */
	private function _insert($table, $data = array(), $replace = FALSE, $returnId = FALSE, $onDuplicate = '')
	{
		$res = $this->_parseData($data);
		if(empty($res['values'])){
			return -1;
		}
		$op = $replace ? 'REPLACE' : 'INSERT';
		$sql = sprintf("%s INTO %s %s VALUES %s", $op, $table, $res['column'], $res['values']);
		if($onDuplicate && !$replace){
			$sql .= " ON DUPLICATE KEY " . $onDuplicate;
		}
//		echo $sql;exit;
		$res = $this->query($sql);
		if($res >= 0 && $returnId){
			return $this->insertId();
		}
		return $res;
	}

	/**
	 * 格式化 INSERT/REPLACE 数组
	 * @param array $data
	 * @return array
	 */
	private function _parseData($data)
	{
		$res = array();
		$deep = $this->_getArrayDeep($data);
		if($deep == 0){
			return $res;
		} elseif($deep == 1){
			$data = array($data);
		}
		$first = array();
		$values = array();
		foreach($data as $first){
			break;
		}
		if(empty($first)){
			return FALSE;
		}
		$column = $this->_makeColumns($first);
		foreach($data as $dat){
			$values[] = $this->_makeValues($dat);
		}
		$res['column'] = $column;
		$res['values'] = implode(",", $values);
		return $res;
	}

	/**
	 * 判断数组的维数，最多只判断二维
	 * @param array $data
	 * @return int
	 */
	private function _getArrayDeep($data)
	{
		if(is_array($data) && $data){
			foreach($data as $d){
				if(is_array($d)){
					if(empty($d)){
						continue;
					} else{
						return 2;
					}
				} else{
					return 1;
				}
			}
		}
		return 0;
	}

	/**
	 * 够在 INSERT/REPLACE 的VALUES部分内容
	 * @param array $data
	 * @return string
	 */
	private function _makeValues($data)
	{
		$dat = array();
		foreach($data as $v){
			$dat[] = sprintf("'%s'", $this->slashes($v));
		}
		return sprintf("(%s)", implode(", ", $dat));
	}

	/**
	 * 够在 INSERT/REPLACE 的COLUMN部分内容
	 * @param array $data
	 * @return string
	 */
	private function _makeColumns($data)
	{
		$dat = array();
		foreach($data as $k => $v){
			if(is_numeric($k)){
				return '';
			}
			$tmp = trim($this->slashes($k));
			if($tmp{0} != '`'){
				$tmp = "`{$tmp}`";
			}
			$dat[] = $tmp;
		}
		return sprintf("(%s)", implode(", ", $dat));
	}
}
