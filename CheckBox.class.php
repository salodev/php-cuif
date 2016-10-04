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
		$layer = $this->getScreenLayer();
		list($x,$y) = $this->getAbsolutePosition();
        $layer->setPos($x, $y);
        $label = $this->label?$this->label.' : ':'';
        $layer->write($label);
        if ($this->_focus) {
            $layer->color('7');
        }
        $layer->write('[' . ($this->checked? 'X' : ' ') . ']');
        $layer->color('0');
	}
}