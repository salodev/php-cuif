<?php

class InputBox extends VisualObject {
    public $x = 0;
    public $y = 0;
    public $width = 32;
    public $label = null;
	public $postLabel = null;
    public $value = null;
    public $hideMask = null;
	public $color = 0;
    private $_cursorPos = null;
	private $_oldValue = null;
    public function getCursorPos() {
        if ($this->_cursorPos===null) {
            $this->_cursorPos = strlen($this->value);
        }
        return $this->_cursorPos;
    }
    public function input(Input $input) {
        if (preg_match('/^[a-z0-9\ \.\,\;\:\_\+\-\*\/\=\!\"\#\$\%\&\(\)\'\?\¡\¿\\\\[\]\{\}]+$/i', $input->raw) /*&& strlen($tecla)==1*/) {
            if ($this->getCursorPos()==0) {
                $this->value = $input->raw . $this->value;
            } else {
                $pts = str_split($this->value, $this->getCursorPos());
                $value = '';
                foreach($pts as $k => $v) {
                    $value .= $v;
                    if ($k==0) {
                        $value .= $input->raw;
                    }
                }
                $this->value = $value;
            }
            $this->_cursorPos+=strlen($input->raw);
        }
        
        if ($input->spec=='ARROW_LEFT') {
            $this->_cursorPos--;
            if ($this->_cursorPos<0) {
                $this->_cursorPos=0;
            }
        }
        if ($input->spec=='ARROW_RIGHT') {
            $this->_cursorPos++;
            if ($this->_cursorPos>strlen($this->value)) {
                $this->_cursorPos = strlen($this->value);
            }
        }
        
        if ($input->spec=='BACKSPACE') {
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
        
        if ($input->spec=='RETURN') {
            $aw = $this->_application->getActiveWindow();
            if ($aw) {
                $aw->nextTabStop();
            }
			$this->trigger('keyReturn');
        }
        
        if ($input->spec=='HOME') {
            $this->_cursorPos = 0;
        }
        
        if ($input->spec=='END') {
            $this->_cursorPos = strlen($this->value);
        }
		if ($this->_oldValue !== $this->value) {
			$this->_oldValue = $this->value;
			$this->trigger('change');
		}
		$this->render();
		$this->trigger('keyPress', $input);
    }
    
    public function render() {
		$layer = $this->getScreenLayer();
		$layer->color($this->color);
		list($x,$y) = $this->getAbsolutePosition();
        $layer->setPos($x, $y);
        $label = $this->label?$this->label.' : ':'';
        $layer->write($label);
        if ($this->_focus) {
            $layer->color('30;47');
            if ($this->_cursorPos===null) {
                $this->_cursorPos = strlen($this->value);
            }
            Console::SetStaticCursorPos($x + strlen($label) + $this->_cursorPos, $y);
        }
        $layer->write(str_pad(($this->hideMask?str_repeat($this->hideMask, strlen($this->value)):$this->value), $this->width, ' ', STR_PAD_RIGHT));
        $layer->color($this->color);
		$layer->write(' ');
		if ($this->postLabel) {
			$layer->write($this->postLabel);
		}
    }
}