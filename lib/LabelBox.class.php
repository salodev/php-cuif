<?php

class LabelBox extends VisualObject {
    public $x = 0;
    public $y = 0;
    public $value = null;
    
    public function render() {
		list($x,$y) = $this->getAbsolutePosition();
        Console::SetPos($x, $y);
        Console::Write($this->value);
    }
}