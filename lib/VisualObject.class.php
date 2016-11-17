<?php

abstract class VisualObject {
	/**
	 *
	 * @var Application; 
	 */
    protected $_application = null;
    protected $_objects = array();
    protected $_focus = false;
    protected $_parentObject = null;
	protected $_eventsHandler = null;
	protected $_screenLayer = null;
	
	protected $_x = 0;
	protected $_y = 0;
	protected $_width = 1;
	protected $_height = 1;
	
	final public function __get($name) {
		$fn = array($this, "__get_{$name}");
		if (is_callable($fn)) {
			return $fn();
		}
		if (isset($this->$name)) {
			return $this->$name;
		}
	}
	
	final public function __set($name, $value) {
		$fn = array($this, "__set_{$name}");
		if (is_callable($fn)) {
			return $fn($value);
		}
		$this->$name = $value;
	}
	
    final public function __construct(Application $application, $parentObject = null, array $params = array()) {
        $this->_application = $application;
		$this->_eventsHandler = new EventsHandler();
        if ($parentObject != null && !($parentObject instanceof VisualObject)) {
            throw new Exception('parentObject must be an VisualObject instance');
        }
        $this->_parentObject = $parentObject;
		$this->getScreenLayer();
		$this->_construct($params);
    }
	
	protected function _construct(array $params = array()) {
		
	}
	
	public function __get_x() {
		return $this->_x;
	}
	
	public function __set_x($x) {
		$this->_x = $x;
	}
	
	public function __get_y() {
		return $this->_y;
	}
	
	public function __set_y($y) {
		$this->_y = $y;
	}
	
	public function __get_width() {
		return $this->_width;
	}
	
	public function __set_width($width) {
		$this->_width = $width;
	}
	
	public function __get_height() {
		return $this->_height;
	}
	
	public function __set_height($height) {
		$this->_height = $height;
	}
	
	public function trigger($eventName, $params = null) {
		$this->_eventsHandler->trigger($eventName, $this, $params);
	}
	
	public function bind($eventName, $eventListener, $persistent = true) {
		$this->_eventsHandler->addListener($eventName, $eventListener, $persistent);
	}
	
	public function keyPress($keySpec, $eventListener, $persistent = true) {
		$this->bind('keyPress', function(Input $input) use ($keySpec, $eventListener) {
			if ($input->spec===$keySpec) {
				$eventListener($input);
			}
		}, $persistent);
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
    
	/**
	 * 
	 * @param string $className
	 * @param array $attributes
	 * @return VisualObject
	 */
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
			$pos = $this->_parentObject->getInnerPosition();
			$parentX = $pos[0];
			$parentY = $pos[1];
		}
		return array($this->x + $parentX, $this->y + $parentY);
	}
	
	public function getInnerPosition() {
		return $this->getAbsolutePosition();
	}
    
    public function openWindow($className) {
        return $this->_application->openWindow($className);
    }
    
	public function input(Input $input) {
		
	}
	
	/**
	 * 
	 * @return ScreenLayer $screenLayer;
	 */
	public function getScreenLayer() {
		if ($this->_screenLayer===null) {
			if ($this->_parentObject!==null) {
				return $this->_parentObject->getScreenLayer();
			}
			$this->_screenLayer = Screen::GetInstance()->createLayer();
		}
		return $this->_screenLayer;
	}
	
	public function getInnerDimensions() {
		return [$this->width, $this->height];
	}
	
    abstract public function render();
}