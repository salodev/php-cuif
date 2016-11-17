<?php

class Window extends VisualObject {
	const NORMAL    = 0;
	const MAXIMIZED = 1;
	const MINIMIZED = 2;
	
    public $tabStop = 0;
	public $windowIndex = null;
	protected $_texts = array();
    protected $_x = 10;
    protected $_y = 10;
    protected $_height = 10;
    protected $_width = 40;
    protected $_title = null;
	protected $_sizeStatus = Window::NORMAL;
	protected $_style = 'double';
	/**
	 *
	 * @var VisualObject
	 */
    protected $_focusedObject = null;
	protected $_color = 0;
	private $_toolKeys = array();
	private $_orignalSizeAndPosition = array();
	private $_autoFocus = true;
	
	public function __get_title() {
		return $this->_title;
	}
	
	public function __set_title($v) {
		$this->_title = $v;
		$this->render();
	}
	
	public function __get_sizeStatus() {
		return $this->_sizeStatus;
	}
	
	public function __set_sizeStatus($v) {
		$this->_sizeStatus = $v;
	}
	
	public function __set_color($v) {
		$this->_color = $v;
	}
	
	public function __get_color() {
		return $this->_color;
	}
	
	public function __set_x($v) {
		$this->getScreenLayer()->clear();
		$this->_x = $v;
		$this->render();
	}
	
	public function __set_y($v) {
		$this->getScreenLayer()->clear();
		$this->_y = $v;
		$this->render();
	}
	
	public function __set_height($v) {
		$this->getScreenLayer()->clear();
		$this->_height = $v;
		$this->render();
	}
	
	public function __set_width($v) {
		$this->getScreenLayer()->clear();
		$this->_width = $v;
		$this->render();
	}
	
	public function init(array $params = array()){
		
	}
	
	public function setAutoFocus() {
		$this->_autoFocus = true;
	}
	
	public function setManualFocus() {
		$this->_autoFocus = false;
	}
	
	public function addToolKey($key, $text) {
		$this->_toolKeys[$key] = $text;
	}
	
	public function setToolKeys(array $toolKeys) {
		$this->_toolKeys = $toolKeys;
	}
	
	public function getToolKeys() {
		return $this->_toolKeys;
	}
	
	/**
	 * 
	 * @return VisualObject
	 */
	public function getFocusedObject() {
		return $this->_focusedObject;
	}
	
	public function addText($string, $x = 1, $y = null) {
		$this->_texts[] = array($string, $x, $y===null?count($this->_texts)+1:$y);
	}
    
    public function addObject(VisualObject $object){
        parent::addObject($object);
        $this->setTabStop(count($this->_objects)-1);
    }
    
    public function setTabStop($index) {
        $this->tabStop = $index;
        if ($this->_focusedObject !== null) {
            $this->_focusedObject->lostFocus();
        }
        $object = $this->_objects[$index];
        $this->_focusedObject = $object;
        $object->focused();
		$this->render();
    }
    
    public function prevTabStop() {
        if ($this->tabStop>0) {
            $this->setTabStop($this->tabStop-1);
        }
    }
    
    public function nextTabStop() {
        if ($this->tabStop < count($this->_objects)-1){
            $this->setTabStop($this->tabStop+1);
        }
    }
	
	public function maximize() {
		$this->_orignalSizeAndPosition = array(
			'x' => $this->x,
			'y' => $this->y,
			'width' => $this->width,
			'height' => $this->height,
		);
		list($screenW, $screenH) = Console::GetDimensions();
		$this->x = 1;
		$this->y = 1;
		$this->width = $screenW;
		$this->height = $screenH-2;
		$this->sizeStatus = Window::MAXIMIZED;
		$this->getScreenLayer()->clear();
		$this->getScreenLayer()->fullScreen = true;
		$this->render();
	}
	
	public function hide() {
		$this->getScreenLayer()->visible = false;
		Screen::GetInstance()->changed();
	}
	
	public function close() {
		Screen::GetInstance()->removeLayer($this->getScreenLayer());
		$this->_application->closeWindow($this);
		Screen::GetInstance()->changed();
		Screen::GetInstance()->refresh();
	}
	
	public function moveToTop() {
		Screen::GetInstance()->topLayer($this->getScreenLayer());
	}
	
	public function restore() {
		$o = (object) $this->_orignalSizeAndPosition;
		$this->x = $o->x;
		$this->y = $o->y;
		$this->width = $o->width;
		$this->height = $o->height;
		$this->sizeStatus = Window::NORMAL;
		$this->getScreenLayer()->clear();
		$this->getScreenLayer()->fullScreen = false;
		$this->render();
	}
    
    public function input(Input $input) {
		$this->trigger('keyPress', $input);
		if ($input->spec) {
			$this->trigger('keyPress_' . $input->spec);
		}
		if ($this->_autoFocus) {
			if ($input->spec == 'ARROW_UP') {
				$this->prevTabStop();
			} elseif ($input->spec == 'ARROW_DOWN' || $input->spec == 'TAB') {
				$this->nextTabStop();
			} elseif ($input->spec == 'F11') {
				if ($this->sizeStatus == Window::MAXIMIZED) {
					$this->restore();
				} elseif($this->sizeStatus == Window::NORMAL) {
					$this->maximize();
				}
			}
        
	        if ($this->_focusedObject !== null) {
	            $this->_focusedObject->input($input);
	        }
		}
    }
	
	public function getScreenLayer() {
		if ($this->_screenLayer == null) {
			$this->_screenLayer = Screen::GetInstance()->createLayer();
		}
		return $this->_screenLayer;
	}
    
    public function render() {
		$layer = $this->getScreenLayer();
		$this->_renderBox($layer);
		foreach($this->_texts as $info) {
			list($text, $x, $y) = $info;
			$this->layer->write($text, $x, $y);
		}
        foreach($this->_objects as $object) {
            $object->render();
        }
		Screen::GetInstance()->changed();
    }
	
	private function _renderBox($layer) {
		if ($this->sizeStatus == Window::MAXIMIZED) {
			$layer->color($this->_color);
			$y = $this->y;
			list($cornerTL,$cornerTR,$cornerBL,$cornerBR,$hLine,$vLine,$teeL,$hSep,$teeR) = Box::GetSymbols($this->_style);
			// $layer->write($cornerTL . str_repeat($hLine, $this->width - 2) . $cornerTR, $this->x, $y);
			// $layer->write($vLine, $this->x, ++$y);
			$layer->write(' ' . str_pad($this->title, $this->width - 2, ' ', STR_PAD_RIGHT) .' ', $this->x, $y);
			//$layer->write($vLine);
			$layer->write(str_repeat($hSep, $this->width) . $teeR, $this->x, ++$y);
			for($i=0;$i<$this->height;$i++) {
				$layer->write(str_repeat(' ', $this->width));
			}
			// $layer->write($cornerBL . str_repeat($hLine, $this->width - 2) . $cornerBR, $this->x, ++$y);
		} else {
			$layer->color($this->_color);
			$y = $this->y;
			list($cornerTL,$cornerTR,$cornerBL,$cornerBR,$hLine,$vLine,$teeL,$hSep,$teeR) = Box::GetSymbols($this->_style);
			$layer->write($cornerTL . str_repeat($hLine, $this->width - 2) . $cornerTR, $this->x, $y);
			$layer->write($vLine, $this->x, ++$y);
			$layer->write(' ' . str_pad($this->title, $this->width - 4, ' ', STR_PAD_RIGHT) .' ');
			$layer->write($vLine);
			$layer->write($teeL . str_repeat($hSep, $this->width - 2) . $teeR, $this->x, ++$y);
			for($i=0;$i<$this->height;$i++) {
				$layer->write($vLine, $this->x, ++$y);
				$layer->write(str_repeat(' ', $this->width - 2));
				$layer->write($vLine);
			}
			$layer->write($cornerBL . str_repeat($hLine, $this->width - 2) . $cornerBR, $this->x, ++$y);
		}
	}
	
	public function getAbsolutePosition() {
		return [$this->x, $this->y];
	}
	
	public function getInnerDimensions() {
		if ($this->sizeStatus == Window::MAXIMIZED){
			return [$this->width, $this->height];
		}
		if ($this->sizeStatus == Window::MINIMIZED){
			throw new Exception('Minimized status not supported yet.');
		}
		return [$this->width-2, $this->height];
	}
	
	public function getInnerPosition() {
		list($x, $y) = $this->getAbsolutePosition();
		if ($this->sizeStatus == Window::MAXIMIZED){
			return [$x, $y+2];
		}
		if ($this->sizeStatus == Window::MINIMIZED){
			throw new Exception('Minimized status not supported yet.');
		}
		return [$x+1, $y+3];
	}
    
	/**
	 * 
	 * @param int $x
	 * @param int $y
	 * @param string $label
	 * @param string $value
	 * @param string $hideMask
	 * @return InputBoxs
	 */
    public function createInputBox($x, $y, $label, $value = null, $hideMask=null, $color = null) {
        return $this->createObject('InputBox', array(
            'x'        => $x,
            'y'        => $y,
            'label'    => $label,
            'value'    => $value,
            'hideMask' => $hideMask,
			'color'    => $color===null?$this->color:$color,
        ));
    }
    
	/**
	 * 
	 * @param int $x
	 * @param int $y
	 * @param string $value
	 * @param string $title
	 * @return LabelBox
	 */
    public function createLabelBox($x, $y, $value) {
        return $this->createObject('LabelBox', array(
            'x'        => $x,
            'y'        => $y,
            'value'    => $value,
        ));
    }

	/**
	 * 
	 * @param int $x
	 * @param int $y
	 * @param string $label
	 * @param boolean $checked
	 * @return CheckBox
	 */
    public function createCheckBox($x, $y, $label, $checked = false) {
        return $this->createObject('CheckBox', array(
            'x'        => $x,
            'y'        => $y,
            'label'    => $label,
            'checked'    => $checked,
        ));
    }
    
	/**
	 * 
	 * @param int $x
	 * @param int $y
	 * @param string $label
	 * @param callable $onPress
	 * @return Button
	 */
    public function createButton($x, $y, $label, $onPress = null) {
        $vo = $this->createObject('Button', array(
            'x'        => $x,
            'y'        => $y,
            'label'    => $label,
        ));
		if ($onPress !== null) {
			$vo->bind('press', $onPress);
		}
		return $vo;
    }
	
	/**
	 * 
	 * @return ListBox
	 */
	public function createListBox() {
		return $this->createObject('ListBox');
	}
}