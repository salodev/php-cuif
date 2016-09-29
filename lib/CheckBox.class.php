<?php

class CheckBox extends VisualObject {
    public $label = null;
    public $checked = false;
    public function input($tecla, $teclaHex) {
        if ($teclaHex==Input::KEY_SPACE) {
            $this->checked = !$this->checked;
        }
    }

    public function render() {
		list($x,$y) = $this->getAbsolutePosition();
        Console::SetPos($x, $y);
        $label = $this->label?$this->label.' : ':'';
        Console::Write($label);
        if ($this->_focus) {
            Console::Color('7');
        }
        Console::Write('[' . ($this->checked? 'X' : ' ') . ']');
        Console::Color('0');
	}
}