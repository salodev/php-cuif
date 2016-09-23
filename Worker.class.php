<?php

class Worker {
    private $_objects = array();
    private $_stopped = false;
    public function sendMessage($message, $messageHex) {
        foreach($this->_objects as $object) {
            $object->input($message, $messageHex);
        }
        return;
        $index = count($this->_objects) -1;
        if ($index <0) {
            return;
        }
        $this->_objects[$index]->input($message, $messageHex);
    }
    
    public function render() {
        foreach($this->_objects as $object) {
            $object->render();
        }
    }
    
    public function addObject($object){
        $this->_objects[] = $object;
    }
    
    public function removeObject($index) {
        unset($this->_objects[$index]);
        $this->_objects = array_values($this->_objects);
    }
    
    public function start() {
        $i = fopen('php://stdin',  'r');
        stream_set_blocking($i, 0);
        // $term = `stty -g`;
        system("stty -icanon");
        $tecla = '';
        $teclaHex = null;
        Console::Clear();
        Console::HideCursor();
        Console::SetStaticCursorPos(null, null);
        $this->render();
        Console::ShowCursorStatic();
        do {
            $teclaHex = bin2hex($tecla);
            if ($this->_stopped) {
                break;
            }
            if ($tecla) {
                $this->sendMessage($tecla, $teclaHex);
                Console::Clear();
                Console::SetStaticCursorPos(null, null);
                $this->render();
                Console::ShowCursorStatic();
            }
            if (!$this->_stopped) {
                $tecla = fread($i,8);
            }
            
            //sleep(1);
        } while(true);
        Console::Clear();
        Console::SetPos(0, 0);
        Console::ShowCursor();
    }
        
    public function stop() {
        $this->_stopped = true;
    }
}