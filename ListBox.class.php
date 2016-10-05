<?php

class ListBox extends VisualObject {
	private $_data = array();
	private $_columns = array();
	private $_selectedIndex = 0;
	private $_holeVPosition  = 0;
	private $_holeHPosition  = 0;
	private $_headerHole = null;
	private $_bodyHole = null;
	public $height = null;
	public $width  = null;
	
	public function __set_x($x) {
		$this->_x = $x;
		$this->_headerHole->x = $x;
		$this->_bodyHole->x = $x;
	}
	
	public function __set_y($y) {
		$this->_y = $y;
		$this->_headerHole->y = $y;
		$this->_bodyHole->y = $y+2;
	}
	
	public function __set_height($height) {
		$this->_height = $height;
		$this->_headerHole->height = 2;
		$this->_bodyHole->height = $height-2;
	}
	
	public function __set_width($width) {
		$this->_width = $width;
		$this->_headerHole->width = $width;
		$this->_bodyHole->width = $width;
	}
	
	protected function _construct(array $params = array()) {
		list($x, $y) = $this->getAbsolutePosition();
		list($width, $height) = $this->_parentObject->getInnerDimensions();
		$this->_headerHole = $this->getScreenLayer()->addHole($x, $y, $width, 2);
		$this->_bodyHole   = $this->getScreenLayer()->addHole($x, $y+2, $width, $height-2);
	}

	public function input($msg, $hexMsg) {
		list(,$height) = $this->getDimensions();
		if ($hexMsg==Input::KEY_ARROW_UP) {
			$this->moveSelection(-1, false);
		}
		if ($hexMsg==Input::KEY_ARROW_DOWN) {
			$this->moveSelection(1, false);
		}
		if ($hexMsg==Input::KEY_ARROW_LEFT) {
			$this->scrollHorizontal(-4, false);
		}
		if ($hexMsg==Input::KEY_ARROW_RIGHT) {
			$this->scrollHorizontal(4, false);
		}
		if ($hexMsg==Input::KEY_PAGE_UP) {
			$this->moveSelection($height*-1, false);
		}
		if ($hexMsg==Input::KEY_PAGE_DOWN) {
			$this->moveSelection($height, false);
		}
		if ($hexMsg==Input::KEY_HOME) {
			$this->moveSelection(0);
		}
		if ($hexMsg==Input::KEY_END) {
			$this->moveSelection(count($this->_data)-1);
		}
	}
	
	public function moveSelection($index = 0, $absolute = true) {
		$this->_bodyHole->setLineColor(0, $this->_selectedIndex+1);
		if ($absolute) {
			$this->_selectedIndex = $index;
		} else {
			$this->_selectedIndex += $index;
		}
		if ($this->_selectedIndex<0) {
			$this->_selectedIndex = 0;
		}
		if ($this->_selectedIndex > count($this->_data)-1) {
			$this->_selectedIndex = count($this->_data)-1;
		}
		if ($this->_selectedIndex < $this->_holeVPosition) {
			$this->_holeVPosition = $this->_selectedIndex;
		}
		if ($this->_selectedIndex > $this->_holeVPosition + $this->_bodyHole->height) {
			$this->_holeVPosition = $this->_selectedIndex - $this->_bodyHole->height;
		}
		$this->_bodyHole->setLineColor('0;30;46', $this->_selectedIndex+1);
		$this->_bodyHole->offsetY = $this->_holeVPosition;
		Screen::GetInstance()->refresh();
	}
	
	public function scrollHorizontal($offset, $absolute = true) {
		if ($absolute) {
			$this->_holeHPosition = $offset;
		} else {
			$this->_holeHPosition += $offset;
		}
		if ($this->_holeHPosition<0) {
			$this->_holeHPosition=0;
		}
		$this->_headerHole->offsetX = $this->_holeHPosition;
		$this->_bodyHole->offsetX   = $this->_holeHPosition;
		Screen::GetInstance()->refresh();
	}

	public function addColumn($title, $name, $width, $align = 1, $visible = true) {
		$this->_columns[] = (object) array(
			'title'   => $title,
			'name'    => $name,
			'width'   => $width,
			'align'   => $align,
			'visible' => $visible,
		);
	}
	public function addRow(array $data) {
		$this->_data[] = $data;
	}
	public function setData(array $data) {
		$this->_data = $data;
	}
	public function clear() {
		$this->_data = array();
	}

	public function hideColumn($index) {
		$this->_columns[$index]['visible'] = false;
	}

	public function showColumn($index) {
		$this->_columns[$index]['visible'] = true;
	}
	
	public function getDataRow($columnName = null) {
		if ($columnName !==null) {
			return $this->_data[$this->_selectedIndex][$columnName];
		}
		return $this->_data[$this->_selectedIndex];
	}
	
	public function getDimensions() {
		$width = $this->width;
		$height = $this->height;
		if ($this->_parentObject != null) {
			list($pw,$ph) = $this->_parentObject->getInnerDimensions();
			if ($width == null) {
				$width = $pw;
			}
			if ($height == null) {
				$height = $ph;
			}
		}
		return [$width, $height];
	}

	public function render() {
		$hpos = 1;
		foreach($this->_columns as $column) {
			$this->_headerHole->color(36);
			$this->_headerHole->setPos($hpos,1);
			$this->_headerHole->write(str_pad($column->title, $column->width, ' ', $column->align));
			$this->_headerHole->color(0);
			$this->_headerHole->write('|');
			$this->_headerHole->setPos($hpos,2);
			$this->_headerHole->write(str_repeat('-', $column->width).'+');
			$hpos += $column->width+1; // because there is a separator char between columns;
		}

		$yp = 1;
		foreach($this->_data as $index => $row) {
			$this->_bodyHole->setPos(1,$yp++);
			if ($index==$this->_selectedIndex) {
				$this->_bodyHole->color('0;30;46');
			}
			foreach($this->_columns as $column) {
				$value = '';
				if (isset($row[$column->name])) {
					$value = $row[$column->name];
				}
				$content = str_pad($value, $column->width, ' ', $column->align);
				$content = substr($content, 0, $column->width);
				$content = str_pad($content, $column->width, ' ', $column->align);
				$this->_bodyHole->write($content . '|');
			}
			$this->_bodyHole->color('0');
		}
	}
}