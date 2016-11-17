<?php
namespace Applications\MysqlClientApplication;

class TableWindow extends \Window{
	/**
	 * Field,Type,Null,Key,Default,Extra
	 */
	public $columns = array();
	public $filters = array();
	public $limit = 100;
	public $offset = 0;
	public function init(array $params = array()) {
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
		$this->x = 35;
		$this->y = 12;
		$this->title = $this->_tableName . ' TABLE';
		$this->maximize();
		/**
		 * @var ListBox
		 */
		$this->list = $this->createObject('ListBox');
		$this->list->setColumnSelectionMode();
		$this->_connection->query('DESCRIBE ' . $this->_tableName, function($rs) {
			$this->_columns = $rs;
			foreach($rs as $row) {
				$this->list->addColumn($row['Field'], $row['Field'], 15);
			}
			$this->render();
			\Screen::GetInstance()->refresh();
			$this->updateList();
		});
		$this->keyPress('F3', function() {
			$this->_application->openWindow(__NAMESPACE__ . '\TableFilterWindow', [
				'tableWindow' => $this,
			]);
		});
	}
	
	public function addFilter($spec, $columnName, $value, $enabled = true) {
		$this->filters[] = new TableFilter($spec, $columnName, $value, $enabled);
	}
	
	public function getFilters() {
		return $this->filters;
	}
	
	public function clearFilters() {
		$this->filters = array();
	}
	
	public function updateList() {
		$wheres = array();
		foreach($this->filters as $filter) {
			if ($filter->enabled) {
				$wheres[] = $filter->getSQL();
			}
		}
		$sqlWhere = '';
		if (count($wheres)) {
			$sqlWhere = 'WHERE ' . implode(' AND ', $wheres);
		}
		$sql = "
			SELECT *
			FROM {$this->_tableName}
			{$sqlWhere}
			LIMIT {$this->offset}, {$this->limit}
		";
		$to = \Timer::Timeout(function() {
			$msg = $this->msgWindow = $this->_application->openWindow();
			$msg->title = 'QUERYING...';
		}, 1000);
		$this->_connection->query($sql)->done(function($rs) use ($to) {
			$this->list->setData($rs);
		})->always(function() use ($to) {
			\Timer::Delete($to);
			if ($this->msgWindow instanceof Window) {
				$this->msgWindow->close();
			}
		});
	}
}

class TableFilter {
	public $spec = null;
	public $columnName = null;
	public $value = null;
	public $enabled = true;
	public function __construct($spec, $columnName, $value, $enabled = true) {
		$this->spec       = $spec;
		$this->columnName = $columnName;
		$this->value      = $value;
		$this->enabled    = $enabled;
	}
	
	public function getSQL() {
		$spec       = $this->spec;
		$columnName = $this->columnName;
		$value      = addslashes($this->value);
		if ($spec=='=') {
			return "{$columnName} = '{$value}'";
		}
		if ($spec=='<>') {
			return "{$columnName} <> '{$value}'";
		}
		if ($spec=='>=') {
			return "{$columnName} >= '{$value}'";
		}
		if ($spec=='<=') {
			return "{$columnName} <= '{$value}'";
		}
		if ($spec=='LIKE') {
			return "{$columnName} LIKE '%{$value}%'";
		}
		if ($spec=='NOT LIKE') {
			return "{$columnName} NOT LIKE '%{$value}%'";
		}
		if ($spec=='MATCH') {
			return "MATCH({$columnName}) AGAINST('{$value}' IN BOOLEAN MODE)";
		}
		if ($spec=='NOT MATCH') {
			return "NOT MATCH({$columnName}) AGAINST('{$value}' IN BOOLEAN MODE)";
		}
		if ($spec=='IN' || $spec=='NOT IN') {
			$values = explode(',', $value);
			$nv = array();
			foreach($values as $v) {
				$nv[] = "'{$v}'";
			}
			$value = implode(',', $nv);
			if ($spec=='IN') {
				return "{$columnName} IN($value)";
			} else {
				return "{$columnName} NOT IN($value)";
			}
		}
		throw new Exception('Not allowed filter');
	}
}