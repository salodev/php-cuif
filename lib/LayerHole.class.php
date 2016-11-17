<?php

class LayerHole extends ScreenLayer{
	public $x = 1;
	public $y = 1;
	public $width = 10;
	public $height = 10;
	public $offsetX = 0;
	public $offsetY = 0;
	public function __construct($x, $y, $width, $height) {
		$this->x = $x;
		$this->y = $y;
		$this->width = $width;
		$this->height = $height;
		for($h=1;$h<=$height;$h++) {
			for($w=1;$w<=$width;$w++){
				// $this->write(' ', $w, $h);
			}
		}
	}
	
	public function setLinecolor($f,$y) {
		for($x=1+$this->offsetX; $x<=$this->width+$this->offsetX;$x++) {
			$key = "{$y};{$x}";
			if (isset($this->_data[$key])) {
				$this->_data[$key][0] = $f;
			}
		}
	}
	
	public function getData() {
		$this->finalData = array();
		for ($y=1;$y<=$this->height;$y++) {
			for ($x=1;$x<=$this->width;$x++) {
				$yShow   = $y+$this->offsetY;
				$xShow   = $x+$this->offsetX;
				$keyShow = "{$yShow};{$xShow}";
				$yAbs    = $this->y-1+$y;
				$xAbs    = $this->x-1+$x;
				$keyAbs  = "{$yAbs};{$xAbs}";
				if (!isset($this->_data[$keyShow])) {
					$this->finalData[$keyAbs] = array(0, 0, ' ');
				} else {
					$this->finalData[$keyAbs] = $this->_data[$keyShow];
				}
			}
		}
	}
}