<?php
namespace Applications\MysqlClientApplication;

class TableWindow extends \Window{
	public function init($params) {
		$this->_connection = $params['connection'];
		$this->_tableName = $params['table'];
		
		/**
		 * Field
		 * Type
		 * Null
		 * Key
		 * Default
		 * Extra
		 */
		$this->_columns = array();
		$this->_wheres = array();
		$this->_limit = 100;
		$this->_offset = 0;
		$this->x = 35;
		$this->y = 12;
		$this->title = $this->_tableName . ' TABLE';
		$this->maximize();
		$this->list = $this->createObject('ListBox');
		$this->list->y = 3;
		$this->_connection->query('DESCRIBE ' . $this->_tableName, function($rs) {
			$this->_columns = $rs;
			foreach($rs as $row) {
				$this->list->addColumn($row['Field'], $row['Field'], 15);
			}
			$this->render();
			\Screen::GetInstance()->refresh();
			$this->updateList();
		});
	}
	
	public function updateList() {
		$sql = "
			SELECT *
			FROM {$this->_tableName}
			LIMIT {$this->_offset}, {$this->_limit}
		";
		$this->_connection->query($sql, function($rs) {
			$this->list->setData($rs);
			$this->render();
			\Screen::GetInstance()->refresh();
		});
	}
}