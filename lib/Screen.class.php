<?php

class Screen {
	private $_layers = array();
	private $_activeLayer = null;
	private $_baseLayer = null;
	private $_endLayer  = null;
	private $_dimensions = null;
	private $_changed = false;
	static private $_instance = null;
	
	/**
	 * 
	 * @return Screen $screen;
	 */
	static public function GetInstance() {
		if (self::$_instance===null){
			self::$_instance = new Screen;
		}
		return self::$_instance;
	}
	
	public function changed() {
		$this->_changed = true;
	}
	
	static public function Put($characters, $x = null, $y = null){
		self::GetInstance()->write($characters, $x, $y);
	}
	
	public function getLayersCount() {
		return count($this->_layers);
	}
	
	public function __construct() {
		$this->createLayer();
	}
	
	public function write($characters, $x = null, $y = null) {
		$this->_activeLayer->write($characters, $x, $y);
	}
	
	public function topLayer(ScreenLayer $layer) {
		$newArr = $this->_layers;
		$this->_layers = array();
		foreach($newArr as $i => $testLayer) {
			if ($testLayer !== $layer) {
				$this->_layers[] = $testLayer;
			}
		}
		$this->_layers[] = $layer;
		$this->_activeLayer = $layer;
	}
	
	public function removeLayer(ScreenLayer $layer) {
		foreach($this->_layers as $k => $l) {
			if ($l === $layer) {
				unset($this->_layers[$k]);
				break;
			}
		}
		if ($this->_activeLayer===$layer) {
			end($this->_layers);
			$this->_activeLayer = current($this->_layers);
			reset($this->_layers);
		}
	}
	
	public function createLayer() {
		$layer = new ScreenLayer();
		$this->_layers[] = $layer;
		$this->_activeLayer = $layer;
		$layer->setIndex(key($this->_layers));
		return $layer;
	}
	
	public function getActiveLayer() {
		return $this->_activeLayer;
	}
	
	public function getLayerByIndex($i) {
		if (!isset($this->_layers[$i])) {
			throw new Exception('Layer offset does not exist');
		}
		return $this->_layers[$i];
	}
	
	public function getDimensions() {
		// return [2,2];
		if ($this->_dimensions===null) {
			$this->_dimensions = Console::GetDimensions();
		}
		return $this->_dimensions;
	}
	
	private function _makeBaseLayer() {
		if ($this->_baseLayer===null) {
			$this->_baseLayer = array();
			list($width, $height) = $this->getDimensions();
			for($y=1; $y<=$height; $y++) {
				for($x=1; $x<=$width; $x++) {
					$this->_baseLayer["{$y};{$x}"] = array(0, 0, ' ');
				}
			}			
		}
	}
	
	public function mergeLayers() {
		$this->_makeBaseLayer();
		$this->_endLayer = $this->_baseLayer;
		
		$al = $this->getActiveLayer();
		if ($al->fullScreen===true) {
			$al->getData();
			$this->_endLayer = array_merge($this->_endLayer, $al->finalData);
		} else {
			foreach($this->_layers as $layer) {
				$layer->getData();
				$this->_endLayer = array_merge($this->_endLayer, $layer->finalData);
			}	
		}
	}
	
	public function refresh($force = false) {
		if (!$this->_changed && $force == false) {
			return;
		}
		$this->_changed = false;
		$ob = '';
		$ts1 = microtime(true);
		$this->mergeLayers();
		$ts2 = microtime(true) - $ts1;
		// file_put_contents('salo.log', "\nGENERAR PANTALLA: {$ts2}\n", FILE_APPEND);
		
		/**
		 * $endLayer[y][x] = [fgc,bgc,content];
		 */
		
		$ts1 = microtime(true);
		Console::SetPos(1, 1);
		list($width, $height) = $this->getDimensions();
		$lastColor = null;
		for($y=1; $y<=$height; $y++) {
			Console::SetPos(1, $y);
			for($x=1; $x<=$width; $x++) {
				$key = "{$y};{$x}";
				if (isset($this->_endLayer[$key])) {
					$data = $this->_endLayer[$key];
					$fc = $data[0];
					$bc = $data[1];
					$ch = $data[2];
					
					if ($fc!==$lastColor) {
						$lastColor = $fc;
						Console::Write($ob);
						$ob = '';
						Console::Color($fc);
					}
					$ob .= $ch;
				} else {
					Console::Write($ob);
					$ob = '';
					Console::Color(0);
					$ob .= ' ';
				}
			}
			Console::Write($ob);
			$ob = '';
		}
		$ts2 = microtime(true) - $ts1;
		// file_put_contents('salo.log', "DIBUJAR PANTALLA: {$ts2}\n\n", FILE_APPEND);
	}
}