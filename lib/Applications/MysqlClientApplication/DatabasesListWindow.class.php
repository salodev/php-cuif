<?php
namespace Applications\MysqlClientApplication;

class DatabasesListWindow extends \Window {
	private $_connection = null;
	public function init(array $params = array()) {
		$this->_connection = $params['connection'];
		$this->list = $this->createObject('ListBox');
		$this->list->addColumn('Database', 'Database', 30);
		$this->setToolKeys(array(
			'ENTER' => 'Listar Tablas',
			'F7'    => 'Crear BBDD',
			'ESC'   => 'Cerrar',
		));
		$this->bind('keyPress', function($params) {
			list($key,$keyHex) = $params;
			if ($keyHex==\Input::KEY_RETURN) {
				$this->_application->openWindow('Applications\MysqlClientApplication\TablesListWindow', array(
					'database' => $this->list->getDataRow('Database'),
					'connection' => $this->_connection,
				));
			}
		});
		$this->_connection->query('SHOW DATABASES', function($rs) {
			$this->list->setData($rs);
			$this->render();
			\Screen::GetInstance()->refresh();
		});
	}
}