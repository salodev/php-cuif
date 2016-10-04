<?php
abstract class Application {
    protected $_objects = array();
	private $_eventsHandler = null;
	
	final public function __construct() {
		$this->_eventsHandler = new EventsHandler();
	}
	
	public function trigger($eventName, $params = null) {
		$this->_eventsHandler->trigger($eventName, $this, $params);
	}
	
	public function bind($eventName, $eventListener, $persistent = true) {
		$this->_eventsHandler->addListener($eventName, $eventListener, $persistent);
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
			$this->_objects[$index]->hide();
            $this->removeObject($index);
        }
    }
    
    final public function input($message, $messageHex) {
        $this->trigger('keyPress', array($message, $messageHex));
        $window = $this->getActiveWindow();
        if ($window) {
            $window->input($message, $messageHex);
        }
    }
    
    final public function render() {
		$layer = Screen::GetInstance()->getLayerByIndex(0);
        foreach($this->_objects as $object) {
            $object->render();
        }
		$aw = $this->getActiveWindow();
		if (!($aw instanceof Window)) {
			return;
		}
		
		$toolKeys = $aw->getToolKeys();
		list(, $cdimY) = Console::GetDimensions();
		$layer->setPos(1, $cdimY);
		foreach($toolKeys as $key => $text) {
			$layer->write("{$key} ");
			$layer->color('7');
			$layer->write(" {$text} ");
			$layer->color(0);
			$layer->write("  ");
		}
		Screen::GetInstance()->refresh();
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
	
	public function getObjectsCount() {
		return count($this->_objects);
	}
    
    public function openWindow($className, array $params = array()) {
        $windowObject = new $className($this, null, $params);
        $this->addObject($windowObject);
        return $windowObject;
    }
	
	public function confirmWindow($text, $fnConfirm = null, $fnCancel = null) {
		$window = $this->openWindow('ConfirmWindow', array(
			'text' => $text,
		));
		if ($fnConfirm !== null) {
			$window->bind('confirm', $fnConfirm);
		}
		if ($fnCancel !== null) {
			$window->bind('cancel', $fnCancel);
		}
		return $window;
	}
    
    public function end() {
		Worker::Stop();
    }
    
    abstract public function main();
}