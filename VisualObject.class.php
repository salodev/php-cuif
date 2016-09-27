<?php

abstract class VisualObject {
    protected $_application = null;
    protected $_objects = array();
    protected $_focus = false;
    protected $_parentObject = null;

    public $x = 0;
    public $y = 0;
	public $xOffset = 0;
	public $yOffset = 0;
	
    final public function __construct(Application $application, $parentObject = null) {
        $this->_application = $application;
        if ($parentObject != null && !($parentObject instanceof VisualObject)) {
            throw new Exception('parentObject must be an VisualObject instance');
        }
        $this->_parentObject = $parentObject;
        $this->init();
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
    
    abstract public function render();
    
    abstract public function input($mensaje, $mensajeHex);
}