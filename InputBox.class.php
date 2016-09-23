<?php

class InputBox extends VisualObject {
    public $x = 0;
    public $y = 0;
    public $width = 32;
    public $label = null;
    public $value = null;
    public $hideMask = null;
    public function input($tecla, $teclaHex) {
        if (preg_match('/[a-z0-9\-\+\*\\\/\.\,\;\:\?\@\!\"\#\$\%\&\(\)\=\¿\_\ ]/i', $tecla) /*&& strlen($tecla)==1*/) {
            $this->value .= $tecla;
        }
        
        if ($teclaHex==Input::KEY_BACKSPACE) {
            $this->value = substr($this->value, 0, -1);
        }
        
        if ($teclaHex==Input::KEY_RETURN) {
            $aw = $this->_application->getActiveWindow();
            if ($aw) {
                $aw->nextTabStop();
            }
        }
    }
    
    public function render() {
        Console::SetPos($this->y, $this->x);
        Console::Write(($this->label?$this->label.' : ':''));
        if ($this->_focus) {
            Console::Color('7');
        }
        Console::Write(str_pad(($this->hideMask?str_repeat($this->hideMask, strlen($this->value)):$this->value), $this->width, '_', STR_PAD_RIGHT));
        Console::Color('0');
    }
}