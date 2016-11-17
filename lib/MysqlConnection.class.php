<?php

class MysqlConnection implements IMysqlConnection {
	private $_link = null;
	private $_host = null;
	private $_user = null;
	private $_password = null;
	private $_dbname = null;
			
	private $_connectionId = null;
	public function __construct($host, $user, $password, $dbname) {
		$this->_host = $host;
		$this->_user = $user;
		$this->_password = $password;
		$this->_dbname = $dbname;
		$this->connect();
	}
	
	public function connect() {
		$this->_link = mysqli_connect($this->_host, $this->_user, $this->_password, $this->_dbname);
		$this->selectDB($this->_dbname);
		$res = mysqli_query($this->_link, "SELECT CONNECTION_ID() AS CID");
		$row = mysqli_fetch_assoc($res);
		$this->_connectionID = $row['CID'];
	}
	
	public function selectDB($dbName) {
		$this->_dbname = $dbName;
		mysqli_select_db($this->_link, $dbName);
	}
	public function query($query, $callback = null) {
		$deferred = new Deferred();
		if (is_callable($callback)) {
			$deferred->done($callback);
		}
		mysqli_store_result($this->_link);
		$ret = mysqli_query($this->_link, $query, MYSQLI_ASYNC);
		if (!$ret) {
			$error = mysqli_error($this->_link);
			$errno = mysqli_errno($this->_link);
			if ($errno == 2006) {
				$this->connect();
			}
			$deferred->reject();
			throw new Exception($error, $errno);
		}
		Worker::AddTask(function($taskIndex) use($deferred) {
			if ($this->poll()) {
				Worker::RemoveTask($taskIndex);
				$result = $this->reapAsyncQuery();
				if (!$result) {
					$deferred->reject();
					throw new Exception(mysqli_error($this->_link), mysqli_errno($this->_link));
				}
				$rs = array();
				while($row = $result->fetch_assoc()) {
					$rs[] = $row;
				}
				$result->free();
				$deferred->resolve($rs);
			};
		}, true , 'MYSQL QUERY RESULT LISTENER');
		return $deferred;
	}
	public function killQuery() {
		$ret = mysqli_kill($this->_link, $this->_connectionId);
		if (!$ret) {
			throw new Exception(mysqli_error($this->_link), mysqli_errno($this->_link));
		}
	}
	public function poll() {
		$links = array($this->_link);
		return mysqli_poll($links , $links , $links , 0, 1);
	}
	public function reapAsyncQuery() {
		return mysqli_reap_async_query($this->_link);
	}
}