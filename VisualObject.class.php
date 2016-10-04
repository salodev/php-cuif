<?php

abstract class VisualObject {
    protected $_application = null;
    protected $_objects = array();
    protected $_focus = false;
    protected $_parentObject = null;
	protected $_eventsHandler = null;
	protected $_screenLayer = null;

    public $x = 0;
    public $y = 0;
	public $xOffset = 0;
	public $yOffset = 0;
	
    final public function __construct(Application $application, $parentObject = null, array $params = array()) {
        $this->_application = $application;
		$this->_eventsHandler = new EventsHandler();
        if ($parentObject != null && !($parentObject instanceof VisualObject)) {
            throw new Exception('parentObject must be an VisualObject instance');
        }
        $this->_parentObject = $parentObject;
		$this->getScreenLayer();
        $this->init($params);
		$this->render();
    }
	
	public function trigger($eventName, $params = null) {
		$this->_eventsHandler->trigger($eventName, $this, $params);
	}
	
	public function bind($eventName, $eventListener, $persistent = true) {
		$this->_eventsHandler->addListener($eventName, $eventListener, $persistent);
	}
    
    public function init(array $params = array()) {}

   
    public function addObject(VisualObject $object){
        $this->_objects[] = $object;
    }
    
    public function removeObject($index) {
        unset($this->_objects[$index]);
        $this->_objects = array_values($this->_objects);
    }
    
    public function focused() {
        $this->_focus = true;
    }
    
    public function isFocused(){
        return $this->_focus;
    }
    
    public function lostFocus() {
        $this->_focus = false;
    }
    
    public function createObject($className, array $attributes = array()) {
        $object= new $className($this->_application, $this);
        foreach($attributes as $attrName => $attrValue) {
            $object->$attrName = $attrValue;
        }
        $this->addObject($object);
        return $object;
    }

	public function getAbsolutePosition() {
		$parentX = 0;
		$parentY = 0;
		if ($this->_parentObject instanceof VisualObject) {
			$pos = $this->_parentObject->getAbsolutePosition();
			$parentX = $pos[0];
			$parentY = $pos[1];
		}
		return array($this->x + $this->xOffset + $parentX, $this->y + $this->yOffset + $parentY);
	}
    
    public function openWindow($className) {
        return $this->_application->openWindow($className);
    }
    
	public function input($mensaje, $mensajeHex) {
		
	}
	
	public function getScreenLayer() {
		if ($this->_screenLayer===null) {
			if ($this->_parentObject!==null) {
				return $this->_parentObject->getScreenLayer();
			}
			$this->_screenLayer = Screen::GetInstance()->createLayer();
		}
		return $this->_screenLayer;
	}
	
    abstract public function render();
}