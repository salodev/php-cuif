<?php
abstract class Application {
    protected $_objects = array();
	/**
	 *
	 * @var Window 
	 */
	protected $_activeWindow;
	/**
	 *
	 * @var Winow
	 */
	protected $_prevActiveWindow;
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
		return $this->_activeWindow;
    }
	
	public function closeWindow(Window $window) {
		$this->removeObject($window);
		if ($this->_activeWindow === $window) {
			$this->autoSetActiveWindow();
		}
	}
    
    public function closeActiveWindow() {
		if ($this->_activeWindow instanceof Window) {
			$this->_activeWindow->close();
		}
		$this->autoSetActiveWindow();
    }
	
	public function autoSetActiveWindow() {
		if (count($this->_objects)) {
			end($this->_objects);
			$lastWindow = current($this->_objects);
			reset($this->_objects);
			$this->setActiveWindow($lastWindow);
		}
	}
    
    final public function input(Input $input) {
        $this->trigger('keyPress', $input);
		if ($input->spec) {
			$this->trigger('keyPress_' . $input->spec);
		}
        $window = $this->getActiveWindow();
        if ($window instanceof Window) {
            $window->input($input);
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
    
    public function removeObject(VisualObject $object) {
		foreach($this->_objects as $index => $testObject) {
			if ($testObject===$object) {
				unset($this->_objects[$index]);
				break;
			}
		}
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
    
	/**
	 * 
	 * @param type $className
	 * @param array $params
	 * @return \Window
	 */
    public function openWindow($className = 'Window', array $params = array()) {
        $window = new $className($this, null, $params);
        $this->addObject($window);
		$this->_prevActiveWindow = $this->_activeWindow;
		$this->setActiveWindow($window);
		$window->init($params);
		$window->render();
        return $window;
    }
	
	public function setActiveWindow(Window $window) {
		$this->_activeWindow = $window;
		$window->moveToTop();
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