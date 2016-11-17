<?php
namespace Applications\MysqlClientApplication;

class MysqlClientApplication extends \Application {
	public function main() {
		$this->openWindow('Applications\MysqlClientApplication\ConnectionsWindow');
		$this->bind('keyPress_ESCAPE', function() {
			if ($this->getObjectsCount()===0) {
				$this->confirmWindow('Desea Salir?', function() {
					$this->end();
				});
			} else {
				$this->closeActiveWindow();
			}
		});
		$this->bind('keyPress_F12', function() {
			\CUIF::$keyDebug = !\CUIF::$keyDebug;
			$this->openTaskWindow();
		});
	}
	
	public function openTaskWindow() {
		$w = $this->openWindow('Window');
		$w->title = 'WORKER TASKS LIST';
		$l = $w->createListBox();
		$l->addColumn('#',          'taskID',     4);
		$l->addColumn('Task Name',  'taskName',   25);
		$l->addColumn('Persistent', 'persistent', 10);
		$w->bind('refresh', function() use($w, $l) {
			$rs = \Worker::GetTasksList();
			$l->clear();
			foreach($rs as $key => $row) {
				$l->addRow(array(
					'taskID'     => $key,
					'taskName'   => $row['taskName'],
					'persistent' => $row['persistent'] ? 'YES' : 'NO',
				));
			}
			$w->render();
			$l->render();
			\Screen::GetInstance()->refresh(true);
		});
		$w->bind('keyPress', function(\Input $input) use ($w) {
			if ($input->spec=='F5') {
				$w->trigger('refresh');
			}
		});
		$w->trigger('refresh');
	}
	
	public function getConnectionsList() {
		$file = dirname(__FILE__).'/login.json';
		if (!is_file($file)) {
			return array();
		}
		return json_decode(file_get_contents($file), true);
	}
	
	public function storeNewConnection($name, $host, $user, $pass, $db) {
		$connectionsList = $this->getConnectionsList();
		if (!is_array($connectionsList)) {
			$connectionsList = array();
		}
		$connectionsList[$name] = array(
			'name'=>$name,
			'host'=>$host,
			'user'=>$user,
			'pass'=>$pass,
			'db'=>$db,
		);
		
		file_put_contents(dirname(__FILE__).'/login.json', json_encode($connectionsList), FILE_IGNORE_NEW_LINES);
	}
}