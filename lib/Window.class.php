<?php

class Window extends VisualObject {
	const NORMAL    = 0;
	const MAXIMIZED = 1;
	const MINIMIZED = 2;
	
    public $tabStop = 0;
	public $windowIndex = null;
    protected $_x = 10;
    protected $_y = 10;
    protected $_height = 10;
    protected $_width = 40;
    protected $_title = null;
	protected $_sizeStatus = Window::NORMAL;
    protected $_focusedObject = null;
	private $_toolKeys = array();
	private $_orignalSizeAndPosition = array();
	
	public function __get_title() {
		return $this->_title;
	}
	
	public function __set_title($v) {
		$this->_title = $v;
	}
	
	public function __get_sizeStatus() {
		return $this->_sizeStatus;
	}
	
	public function __set_sizeStatus($v) {
		$this->_sizeStatus = $v;
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
		$this->height = $screenH-4;
		$this->sizeStatus = Window::MAXIMIZED;
		$this->getScreenLayer()->clear();
		$this->getScreenLayer()->fullScreen = true;
		$this->render();
	}
	
	public function hide() {
		Screen::GetInstance()->removeTopLayer();
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
    
    public function input($tecla, $teclaHex) {
        if ($teclaHex == Input::KEY_ARROW_UP) {
            $this->prevTabStop();
        } elseif ($teclaHex == Input::KEY_ARROW_DOWN || $teclaHex == Input::KEY_TAB) {
            $this->nextTabStop();
		} elseif ($teclaHex == Input::KEY_F11) {
			if ($this->sizeStatus == Window::MAXIMIZED) {
				$this->restore();
			} elseif($this->sizeStatus == Window::NORMAL) {
				$this->maximize();
			}
		}
        
        if ($this->_focusedObject !== null) {
            $this->_focusedObject->input($tecla, $teclaHex);
        }
		
		$this->trigger('keyPress', array($tecla,$teclaHex));
    }
	
	public function getScreenLayer() {
		if ($this->_screenLayer == null) {
			$this->_screenLayer = Screen::GetInstance()->createLayer();
		}
		return $this->_screenLayer;
	}
    
    public function render() {
		$layer = $this->getScreenLayer();
		$y = $this->y;
        $layer->write('+' . str_repeat('-', $this->width - 2) .'+', $this->x, $y);
        $layer->write('| ' . str_pad($this->title, $this->width - 4, ' ', STR_PAD_RIGHT) .' |', $this->x, ++$y);
        $layer->write('+' . str_repeat('-', $this->width - 2) .'+', $this->x, ++$y);
        for($i=1;$i<=$this->height;$i++) {            
            $layer->write('|' . str_repeat(' ', $this->width - 2) . '|', $this->x, ++$y);
        }
        $layer->write('+' . str_repeat('-', $this->width - 2) .'+', $this->x, ++$y);
        foreach($this->_objects as $object) {
            $object->render();
        }
    }
	
	public function getAbsolutePosition() {
		return [$this->x, $this->y];
	}
	
	public function getInnerDimensions() {
		return [$this->width-2, $this->height-3];
	}
	
	public function getInnerPosition() {
		list($x, $y) = $this->getAbsolutePosition();
		return [$x+1, $y+3];
	}
    
    public function createInputBox($x, $y, $label, $value = null, $hideMask=null) {
        return $this->createObject('InputBox', array(
            'x'        => $x,
            'y'        => $y,
            'label'    => $label,
            'value'    => $value,
            'hideMask' => $hideMask,
        ));
    }
    
    public function createLabelBox($x, $y, $value, $title = null) {
        return $this->createObject('LabelBox', array(
            'x'        => $x,
            'y'        => $y,
            'value'    => $value,
            'title'    => $title,
        ));
    }

    public function createCheckBox($x, $y, $label, $checked = false) {
        return $this->createObject('CheckBox', array(
            'x'        => $x,
            'y'        => $y,
            'label'    => $label,
            'checked'    => $checked,
        ));
    }
    
    public function createButton($x, $y, $label, $onPress = null) {
        return $this->createObject('Button', array(
            'x'        => $x,
            'y'        => $y,
            'label'    => $label,
            'onPress'  => $onPress,
        ));
    }
}