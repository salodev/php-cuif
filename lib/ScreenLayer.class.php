<?php

class ScreenLayer {
	protected $_data = array();
	private $_index = null;
	private $_fcolor = null;
	private $_bcolor = null;
	private $_cursorX = 1;
	private $_cursorY = 1;
	private $_holes = array();
	public $fullScreen = false;
	public $finalData = array();
	
	public function addHole($x, $y, $width, $height) {
		$hole = new LayerHole($x, $y, $width, $height);
		$this->_holes[] = $hole;
		return $hole;
	}
	
	public function setIndex($i) {
		$this->_index = $i;
	}
		
	public function getIndex() {
		return $this->_index;
	}
	
	public function color($color) {
		$this->_fcolor = $color;
	}
	
	public function bColor($color) {
		$this->_bcolor = $color;
	}
	
	public function setPos($x, $y) {
		$this->_cursorX = $x;
		$this->_cursorY = $y;
	}
	
	public function clear() {
		$this->_data = array();
	}
	
	public function write($characters, $x = null, $y = null) {
		if ($x !==null&&$y!==null){
			$this->setPos($x, $y);
		} else {
			$x = $this->_cursorX;
			$y = $this->_cursorY;
		}
		$arrCharacters = str_split($characters);
		foreach($arrCharacters as $i => $ch) {
			$yAbs = $y;
			$xAbs = $x + $i;
			$this->_data["{$yAbs};{$xAbs}"] = [$this->_fcolor, $this->_bcolor, $ch];
		}
		$this->_cursorX +=strlen($characters);
	}
	
	public function getData() {
		$this->finalData = $this->_data;
		foreach($this->_holes as $hole) {
			$hole->getData();
			$this->finalData = array_merge($this->finalData, $hole->finalData);
		}
	}
}