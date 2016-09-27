<?php
class InputBox extends VisualObject {
    public $x = 0;
    public $y = 0;
    public $width = 32;
    public $label = null;
    public $value = null;
    public $hideMask = null;
    private $_cursorPos = null;
    public function getCursorPos() {
        if ($this->_cursorPos===null) {
            $this->_cursorPos = strlen($this->value);
        }
        return $this->_cursorPos;
    }
    public function input($tecla, $teclaHex) {
        if (preg_match('/^[a-z0-9\ \.\,\;\:\_\+\-\*\/\=\!\"\#\$\%\&\(\)\'\?\¡\¿\\\\[\]\{\}]+$/i', $tecla) /*&& strlen($tecla)==1*/) {
            if ($this->getCursorPos()==0) {
                $this->value = $tecla . $this->value;
            } else {
                $pts = str_split($this->value, $this->getCursorPos());
                $value = '';
                foreach($pts as $k => $v) {
                    $value .= $v;
                    if ($k==0) {
                        $value .= $tecla;
                    }
                }
                $this->value = $value;
            }
            $this->_cursorPos+=strlen($tecla);
        }
        
        if ($teclaHex==Input::KEY_ARROW_LEFT) {
            $this->_cursorPos--;
            if ($this->_cursorPos<0) {
                $this->_cursorPos=0;
            }
        }
        if ($teclaHex==Input::KEY_ARROW_RIGHT) {
            $this->_cursorPos++;
            if ($this->_cursorPos>strlen($this->value)) {
                $this->_cursorPos = strlen($this->value);
            }
        }
        
        if ($teclaHex==Input::KEY_BACKSPACE) {
            if ($this->getCursorPos()>0) {
                $pts = str_split($this->value, $this->getCursorPos());
                $value = '';
                foreach($pts as $k=> $v) {
                    if ($k==0) {
                        $value = substr($v, 0, -1);
                    } else {
                        $value .= $v;
                    }
                }
                $this->value = $value;
                $this->_cursorPos--;
            }
        }
        
        if ($teclaHex==Input::KEY_RETURN) {
            $aw = $this->_application->getActiveWindow();
            if ($aw) {
                $aw->nextTabStop();
            }
        }
        
        if ($teclaHex==Input::KEY_HOME) {
            $this->_cursorPos = 0;
        }
        
        if ($teclaHex==Input::KEY_END) {
            $this->_cursorPos = strlen($this->value);
        }
    }
    
    public function render() {
		list($x,$y) = $this->getAbsolutePosition();
        Console::SetPos($x, $y);
        $label = $this->label?$this->label.' : ':'';
        Console::Write($label);
        if ($this->_focus) {
            Console::Color('7');
            if ($this->_cursorPos===null) {
                $this->_cursorPos = strlen($this->value);
            }
            Console::SetStaticCursorPos($x, $y + strlen($label) + $this->_cursorPos);
        }
        Console::Write(str_pad(($this->hideMask?str_repeat($this->hideMask, strlen($this->value)):$this->value), $this->width, '_', STR_PAD_RIGHT));
        Console::Color('0');
    }
}