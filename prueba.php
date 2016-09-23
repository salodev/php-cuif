#!/usr/bin/php
<?php
require_once(dirname(__FILE__).'/bootstrap.php');

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
        
        $this->inputHost = $this->createInputBox(12, 14, 'HOSTNAME', $this->login['host']);        
        $this->inputDB   = $this->createInputBox(12, 15, 'DATABASE', $this->login['db'  ]);
        $this->inputUser = $this->createInputBox(12, 16, 'USERNAME', $this->login['user']);        
        $this->inputPass = $this->createInputBox(12, 17, 'USERPASS', $this->login['pass'], '*');
        
        $this->buttonLogin   = $this->createButton(12, 18, 'LOGIN',  array($this,'onLoginOkPress'));
        $this->buttonCancel  = $this->createButton(22, 18, 'CANCEL', array($this,'onLoginCancelPress'));
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
        
        $window->createButton(12, 18, 'YES', array($this,'onConfirmClosePress'));        
        $window->createButton(20, 18, 'NO', array($this,'onCancelClosePress'));
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
        $y = 19;
        while($ret = mysql_fetch_assoc($res)) {
            $this->createButton(17, $y++, str_pad($ret['Database'], 30, ' ', STR_PAD_RIGHT));
        }
        $this->setTabStop(0);
        $this->height = 30;
    }
}

class KeyDebugger {
    private $_tecla = null;
    private $_teclaHex = null;
    public function render() {
        Console::Write("Printable Pressed Key: {$this->_tecla}",1,1);
        Console::Write("HEX Pressed Key      : {$this->_teclaHex}",2,1);
    }
    
    public function input($message, $messageHex) {
        $this->_tecla = $message;
        $this->_teclaHex = $messageHex;
    }
}

class MyApplication extends Application {
    
    public function main() {
        $this->openWindow('MyLoginWindow');
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

$worker = new Worker();
$myApp = new MyApplication($worker);
$myApp->main();
$worker->addObject(new KeyDebugger());
$worker->addObject($myApp);

$worker->start();
