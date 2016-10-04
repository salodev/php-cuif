<?php

class LabelBox extends VisualObject {
    public $x = 0;
    public $y = 0;
    public $value = null;
    
    public function render() {
		$layer = $this->getScreenLayer();
		list($x,$y) = $this->getAbsolutePosition();
        $layer->setPos($x, $y);
        $layer->write($this->value);
    }
}