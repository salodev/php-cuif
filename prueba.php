#!/opt/php5-6/bin/php
<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');

class MyListWindow extends Window {
	public function init() {
		$this->title = 'LIST WINDOW';
		$this->width = 64;
		$this->height = 16;
		$listBox = $this->listBox = $this->createObject('ListBox');
		$listBox->addColumn('ID', 'id', 10, 1, true);
		$listBox->addColumn('Titulo', 'title', 10, 1, true);
		$listBox->addColumn('Nombre', 'name', 10, 1, true);
		$listBox->addColumn('Ancho', 'width', 10, 0, true);
		$listBox->addColumn('Align', 'align', 10, 1, true);
		$listBox->addColumn('Visible', 'visible', 10, 1, true);
		for ($i=0;$i<30;$i++) {
			$listBox->addRow(array(
				'id' => $i+1,
				'title'=> 'Nombre',
				'name' => 'name',
				'width' => 10,
				'align' => 'RIGHT',
				'visible' => 'TRUE',
			));
		}
	}
}

class MyLoginWindow extends Window {
    public function init() {
        $this->title = 'LOGIN';
        $this->width = 64;
        $this->login = array(
            'host' => 'localhost',
            'db' => '',
            'user' => '',
            'pass' => '',
        );
        if (is_file('login.json')) {
            $this->login = json_decode(file_get_contents('login.json'),true);
        }
        
        $this->inputHost = $this->createInputBox(2, 1, 'HOSTNAME  ', $this->login['host']);
        $this->inputDB   = $this->createInputBox(2, 2, 'DATABASE  ', $this->login['db'  ]);
        $this->inputUser = $this->createInputBox(2, 3, 'USERNAME  ', $this->login['user']);
        $this->inputPass = $this->createInputBox(2, 4, 'USERPASS  ', $this->login['pass'], '*');
        $this->saveLogin = $this->createCheckBox(2, 5, 'SAVE LOGIN', false);
        
        $this->buttonLogin   = $this->createButton(2, 7, 'LOGIN',  array($this,'onLoginOkPress'));
        $this->buttonCancel  = $this->createButton(12, 7, 'CANCEL', array($this,'onLoginCancelPress'));
        $this->setTabStop(0);
    }
    public function onLoginOkPress() {
        file_put_contents('login.json', json_encode(array(
            'host' => $this->inputHost->value,
            'db'   => $this->inputDB->value,
            'user' => $this->inputUser->value,
            'pass' => $this->inputPass->value,
        )), FILE_IGNORE_NEW_LINES);
        $this->_application->dbConnectResource = mysql_connect($this->inputHost->value, $this->inputUser->value, $this->inputPass->value);
        mysql_select_db($this->inputDB->value, $this->_application->dbConnectResource);
        $this->openWindow('DBListWindow');
    }
    
    public function onLoginCancelPress() {
        $window = $this->openWindow('Window');
        $window->title = "SURE ?";
		$window->x = 15;
		$window->y = 15;
        
        $window->createButton(2, 2, 'YES', array($this,'onConfirmClosePress'));        
        $window->createButton(12, 2, 'NO', array($this,'onCancelClosePress'));
    }
       
    public function onConfirmClosePress() {
        $this->_application->end();
    }
    
    
    public function onCancelClosePress() {
        $this->_application->closeActiveWindow();
    }
}

class DBListWindow extends Window {
    public function init() {
        $this->title="DATABASES LIST";
        $this->x=15;
        $this->y=15;
        $res = mysql_query("SHOW DATABASES", $this->_application->dbConnectResource);
        $y = 0;
        while($ret = mysql_fetch_assoc($res)) {
            $this->createButton(0, $y++, str_pad($ret['Database'], 30, ' ', STR_PAD_RIGHT));
        }
        $this->setTabStop(0);
        $this->height = 30;
    }
}

class KeyDebugger {
    private $_tecla = null;
    private $_teclaHex = null;
    public function render() {
        Console::Write("Printable Pressed Key: {$this->_tecla}",0,1);
        Console::Write("HEX Pressed Key      : {$this->_teclaHex}",0,2);
    }
    
    public function input($message) {
        $this->_tecla = $message;
        $this->_teclaHex = bin2hex($message);
    }
}

class MyApplication extends Application {
    
    public function main() {
        //$this->openWindow('MyLoginWindow');
		$this->openWindow('MyListWindow');
    }
    
    public function onMessage($message, $messageHex) {
        
        if ($messageHex == Input::KEY_ESCAPE) {
            $index = count($this->_objects) - 1;
            if ($index>-1) {
                $this->removeObject($index);
            } else {
                $this->end();
            }
        }
    }
}

$con = new MysqlConnection('localhost', 'root', '', 'mysql');
$con->query("SELECT 'hola diana'", function($rs) {
	Worker::AddTask(function() use ($rs) {
		Console::Clear();
		Console::SetPos(1,1);
		print_r($rs);
		Console::Write('Press CONTROL+C to exit');
	
	}, true);
});
Worker::Start();
// CUIF::StartApplication('MyApplication');
