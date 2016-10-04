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
		$this->maximize();
		$this->list = $this->createObject('ListBox');
		$this->list->addColumn('Tabla',   'Name',           30   );
		$this->list->addColumn('Engine',  'Engine',         20   );
		$this->list->addColumn('Rows',    'Rows',           10, 0);
		$this->list->addColumn('A/I',     'Auto_increment', 10, 0);
		$this->list->addColumn('Created', 'Create_time',    19   );
		$this->list->addColumn('Updated', 'Update_time',    19   );
		$this->setToolKeys(array(
			'ENTER' => 'Mostrar Tabla',
			'F7'    => 'Crear Tabla',
			'ESC'   => 'Cerrar',
		));
		$this->_connection->selectDB($this->_database);
		$this->_connection->query('SHOW TABLE STATUS', function($rs) {
			$this->list->setData($rs);
			$this->render();
			\Screen::GetInstance()->refresh();
		});
		$this->bind('keyPress', function($params) {
			list($key,$keyHex) = $params;
			if ($keyHex==\Input::KEY_RETURN) {
				$this->_application->openWindow('Applications\MysqlClientApplication\TableWindow', array(
					'connection' => $this->_connection,
					'table'      => $this->list->getDataRow('Name'),
				));
			}
		});
	}
}