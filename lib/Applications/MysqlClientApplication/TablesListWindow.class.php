<?php
namespace Applications\MysqlClientApplication;

class TablesListWindow extends \Window {
	private $_connection = null;
	private $_database = null;
	public function init(array $params = array()) {
		$this->_connection = $params['connection'];
		$this->_database = $params['database'];
		$this->title = 'TABLES ON ' . $params['database'];
		$this->setManualFocus();
		$this->x=20;
		$this->y=20;
		$this->maximize();
		$this->searchBox = $this->createInputBox(0, 0, 'ENTER TABLE NAME');
		$this->list = $this->createObject('ListBox');
		$this->list->y=1;
		$this->list->addColumn('Table',   'Name',           30   );
		$this->list->addColumn('Engine',  'Engine',         20   );
		$this->list->addColumn('Rows',    'Rows',           10, 0);
		$this->list->addColumn('A/I',     'Auto_increment', 10, 0);
		$this->list->addColumn('Created', 'Create_time',    19   );
		$this->list->addColumn('Updated', 'Update_time',    19   );
		$this->setToolKeys([
			'ENTER' => 'Mostrar Tabla',
			'F7'    => 'Crear Tabla',
			'ESC'   => 'Cerrar',
		]);
		$this->_connection->selectDB($this->_database);
		$this->_connection->query('SHOW TABLE STATUS', function($rs) {
			$this->list->setData($rs);
			$this->render();
			\Screen::GetInstance()->refresh();
		});
		$this->bind('keyPress', function(\Input $input) {
			if (in_array($input->spec, [
				'ARROW_UP',
				'ARROW_DOWN',
				'ARROW_LEFT',
				'ARROW_RIGHT',
				'HOME',
				'END',
				'PAGE_UP',
				'PAGE_DOWN',
				'RETURN',
			])) {
				$this->list->input($input);
			} else {
				$this->searchBox->input($input);
			}
		});
		$this->searchBox->bind('change', function() {
			$search = trim($this->searchBox->value);
			if ($this->_searchTimeout) {
				\Timer::Delete($this->_searchTimeout);
			}
			$this->_searchTimeout = \Timer::TimeOut(function() use($search) {
				$query = $search ?
						"SHOW TABLE STATUS LIKE '%{$search}%'" :
						'SHOW TABLE STATUS';
				$this->_connection->query($query, function($rs) {
					$this->list->setData($rs);
				});
			}, 500);
		});
		$this->list->keyPress('RETURN', function(\Input $input) {
			$this->_application->openWindow(__NAMESPACE__ . '\TableWindow', [
				'connection' => $this->_connection,
				'table'      => $this->list->getRowData('Name'),
			]);
		});
	}
}