<?php
namespace Applications\MysqlClientApplication;

class TablesListWindow extends \Window {
	private $_connection = null;
	private $_database = null;
	public function init(array $params = array()) {
		$this->_connection = $params['connection'];
		$this->_database = $params['database'];
		$this->x=20;
		$this->y=20;
		$this->list = $this->createObject('ListBox');
		$this->list->addColumn('Tabla', 'name', 30);
		$this->setToolKeys(array(
			'ENTER' => 'Mostrar Tabla',
			'F7'    => 'Crear Tabla',
			'ESC'   => 'Cerrar',
		));
		$this->_connection->selectDB($this->_database);
		$this->_connection->query('SHOW TABLES', function($rs) {
			foreach($rs as $row) {
				$this->list->addRow(array(
					'name' => $row['Tables_in_' . $this->_database],
				));
			}
			$this->render();
		});
	}
}