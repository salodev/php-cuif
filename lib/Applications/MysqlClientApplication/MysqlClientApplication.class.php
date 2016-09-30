<?php
namespace Applications\MysqlClientApplication;

class MysqlClientApplication extends \Application {
	public function main() {
		$this->openWindow('Applications\MysqlClientApplication\ConnectionsWindow');
		$this->bind('keyPress', function($params, $source) {
			list(,$teclaHex)=$params;
			if ($teclaHex==\Input::KEY_ESCAPE) {
				if ($this->getObjectsCount()===0) {
					$this->confirmWindow('Desea Salir?', function() {
						$this->end();
					});
				} else {
					$this->closeActiveWindow();
				}
			}
		});
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