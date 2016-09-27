<?php
abstract class Application {
    protected $_worker = null;
    protected $_objects = array();
    
    final public function __construct(Worker $worker) {
        $this->_worker = $worker;
    }
    
    public function getActiveWindow() {
        $index = count($this->_objects) -1;
        if ($index <0) {
            return false;
        }
        
        if ($this->_objects[$index] instanceof VisualObject) {
            return $this->_objects[$index];
        }
        
        return false;
    }
    
    public function closeActiveWindow() {
        $index = count($this->_objects) -1;
        if ($index <0) {
            return false;
        }
        
        if ($this->_objects[$index] instanceof VisualObject) {
            $this->removeObject($index);
        }
    }
    
    final public function input($message, $messageHex) {
        $this->onMessage($message, $messageHex);
        $window = $this->getActiveWindow();
        if ($window) {
            $window->input($message, $messageHex);
        }
    }
    
    final public function render() {
        foreach($this->_objects as $object) {
            $object->render();
        }
    }
    
    public function onMessage($message, $messageHex) {
        
    }
    
    public function addObject(VisualObject $object){
        $this->_objects[] = $object;
    }
    
    public function removeObject($index) {
        unset($this->_objects[$index]);
        $this->_objects = array_values($this->_objects);
    }
    
    public function show(VisualObject $object) {
        $this->addObject($object);
    }
    
    public function createObject($className, array $attributes = array()) {
        $object= new $className($this->_application);
        foreach($attributes as $attrName => $attrValue) {
            $object->$attrName = $attrValue;
        }
        $this->addObject($object);
    }
    
    public function openWindow($className) {
        $windowObject = new $className($this);
        $this->addObject($windowObject);
        return $windowObject;
    }
    
    public function end() {
        $this->_worker->stop();
    }
    
    abstract public function main();
}