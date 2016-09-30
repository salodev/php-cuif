<?php
namespace Applications\MysqlClientApplication;

class ConnectionsWindow extends \Window {
	public function init() {
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
		$self = $this;
		$this->bind('keyPress', function($params, $source) use($self) {
			list($key,$keyHex)=$params;
			if ($key=='+') {
				$window = $self->_application->openWindow('Applications\MysqlClientApplication\AddConnectionWindow');
				$window->bind('saved', function($params, $source) use ($self) {
					$self->_application->closeActiveWindow();
					$self->refreshList();
				});
			}
			if ($keyHex==\Input::KEY_F2) {
				$row = $this->list->getDataRow();
				$window = $this->_application->openWindow('Applications\MysqlClientApplication\AddConnectionWindow', array(
					'name'=>$row['name'],
					'host'=>$row['host'],
					'user'=>$row['user'],
					'pass'=>$row['pass'],
					'db'  =>$row['db'  ],
				));
				$window->bind('saved', function($params, $source) use ($self) {
					$self->_application->closeActiveWindow();
					$self->refreshList();
				});
			}
			if ($keyHex==\Input::KEY_RETURN) {
				$row = $this->list->getDataRow();
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