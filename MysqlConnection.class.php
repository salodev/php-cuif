<?php

class MysqlConnection {
	private $_link = null;
	public function __construct($host, $user, $password, $dbname) {
		$this->_link = mysqli_connect($host, $user, $password, $dbname);
	}
	public function selectDB($dbName) {
		mysqli_select_db($this->_link, $dbName);
	}
	public function query($query, $callback) {
		$ret = mysqli_query($this->_link, $query, MYSQLI_ASYNC);
		if (!$ret) {
			throw new Exception(mysqli_error($this->_link), mysqli_errno($this->_link));
		}
		$conn = $this;
		Worker::AddTask(function() use($conn, $callback) {
			if ($conn->poll()) {
				$result = $conn->reapAsyncQuery();
				if (!$result) {
					throw new Exception(mysqli_error($this->_link), mysqli_errno($this->_link));
				}
				$rs = array();
				while($row = $result->fetch_assoc()) {
					$rs[] = $row;
				}
				$result->free();
				$callback($rs);
			};
		}, true);
	}
	public function poll() {
		$links = array($this->_link);
		return mysqli_poll($links , $links , $links , 0, 1);
	}
	public function reapAsyncQuery() {
		return mysqli_reap_async_query($this->_link);
	}
}