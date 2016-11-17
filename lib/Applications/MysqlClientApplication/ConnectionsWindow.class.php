<?php
namespace Applications\MysqlClientApplication;

class ConnectionsWindow extends \Window {
	public function init(array $params = array()) {
		$this->x = 5;
		$this->y = 5;
		$this->width=64;
		$this->height=16;
		$this->title = 'CONEXIONES';
		$list = $this->list = $this->createObject('ListBox');
		$list->addColumn('Nombre','name', 15);
		$list->addColumn('Host','host', 15);
		$list->addColumn('Usuario','user', 15);
		$this->setToolKeys(array(
			'+'     => 'Nueva Conexion',
			'DEL'   => 'Borrar',
			'F2'    => 'Editar',
			'ENTER' => 'Login',
		));
		$this->refreshList();
		$this->bind('keyPress', function(\Input $input) {
			if ($input->raw=='+') {
				$window = $this->_application->openWindow('Applications\MysqlClientApplication\AddConnectionWindow');
				$window->bind('saved', function($params, $source) {
					$this->_application->closeActiveWindow();
					$this->refreshList();
				});
			}
			if ($input->spec=='F2') {
				$row = $this->list->getRowData();
				$window = $this->_application->openWindow('Applications\MysqlClientApplication\AddConnectionWindow', array(
					'name'=>$row['name'],
					'host'=>$row['host'],
					'user'=>$row['user'],
					'pass'=>$row['pass'],
					'db'  =>$row['db'  ],
				));
				$window->bind('saved', function($params, $source) {
					$this->_application->closeActiveWindow();
					$this->refreshList();
				});
			}
			if ($input->spec=='RETURN') {
				$row = $this->list->getRowData();
				$connection = new \MysqlConnection($row['host'], $row['user'], $row['pass'], $row['db']);
				$this->_application->openWindow('Applications\MysqlClientApplication\DatabasesListWindow', array(
					'connection' => $connection,
				));
			}
		});
	}
	
	public function refreshList() {
		$rs = $this->_application->getConnectionsList();
		$this->list->clear();
		foreach($rs as $row) {
			$this->list->addRow($row);
		}
	}
}