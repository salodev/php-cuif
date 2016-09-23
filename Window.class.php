<?php

class Window extends VisualObject {
    public $tabStop = 0;
    public $x = 10;
    public $y = 10;
    public $height = 10;
    public $width = 40;
    public $title = null;
    protected $_focusedObject = null;
    
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
    
    public function input($tecla, $teclaHex) {
        if ($teclaHex == Input::KEY_ARROW_UP) {
            $this->prevTabStop();
        } elseif ($teclaHex == Input::KEY_ARROW_DOWN || $teclaHex == Input::KEY_TAB) {
            $this->nextTabStop();
        }
        
        if ($this->_focusedObject !== null) {
            $this->_focusedObject->input($tecla, $teclaHex);
        }
    }
    
    public function render() {
        Console::Write('+' . str_repeat('-', $this->width - 2) .'+', $this->y+0, $this->x);
        Console::Write('| ' . str_pad($this->title, $this->width - 4, ' ', STR_PAD_RIGHT) .' |', $this->y+1, $this->x);
        Console::Write('+' . str_repeat('-', $this->width - 2) .'+', $this->y+2, $this->x);
        for($i=1;$i<$this->height;$i++) {            
            Console::Write('|' . str_repeat(' ', $this->width - 2) . '|', $this->y+$i+2, $this->x);
        }
        Console::Write('+' . str_repeat('-', $this->width - 2) .'+', $this->y+$this->height+2, $this->x);
        foreach($this->_objects as $object) {
            $object->render();
        }
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
    
    public function createButton($x, $y, $label, $onPress = null) {
        return $this->createObject('Button', array(
            'x'        => $x,
            'y'        => $y,
            'label'    => $label,
            'onPress'  => $onPress,
        ));
    }
}