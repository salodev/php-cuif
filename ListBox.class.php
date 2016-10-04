<?php

class ListBox extends VisualObject {
	private $_data = array();
	private $_columns = array();
	private $_selectedIndex = 0;
	private $_holeVPosition  = 0;
	private $_holeHPosition  = 0;
	private $_layerHole = null;
	public $height = null;
	public $width  = null;

	public function input($msg, $hexMsg) {
		$render = false;
		$height = $this->height;
		if ($this->_parentObject != null) {
			if ($height == null) {
				$height = $this->_parentObject->height-3;
			}
		}
		if ($hexMsg==Input::KEY_ARROW_UP) {
			$this->_selectedIndex--;
			$render = true;
		}
		if ($hexMsg==Input::KEY_ARROW_DOWN) {
			$this->_selectedIndex++;
			$render = true;
		}
		if ($hexMsg==Input::KEY_ARROW_LEFT) {
			$this->_holeHPosition-=4;
		}
		if ($hexMsg==Input::KEY_ARROW_RIGHT) {
			$this->_holeHPosition+=4;
		}
		if ($hexMsg==Input::KEY_PAGE_UP) {
			$this->_selectedIndex-=$height;
			$render = true;
		}
		if ($hexMsg==Input::KEY_PAGE_DOWN) {
			$this->_selectedIndex+=$height;
			$render = true;
		}
		if ($hexMsg==Input::KEY_HOME) {
			$this->_selectedIndex=0;
			$render = true;
		}
		if ($hexMsg==Input::KEY_END) {
			$this->_selectedIndex=count($this->_data)-1;
			$render = true;
		}
		if ($this->_holeHPosition<0) {
			$this->_holeHPosition=0;
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
		if ($this->_selectedIndex > $this->_holeVPosition + $height) {
			$this->_holeVPosition = $this->_selectedIndex - $height;
		}
		if (true) {
			$this->render();
		}
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
	
	public function _getLayerHole() {
		return $this->_layerHole;
	}

	public function render() {
		list($x,$y)=$this->getAbsolutePosition();
		$width = $this->width;
		$height = $this->height;
		if ($this->_parentObject != null) {
			if ($width == null) {
				$width = $this->_parentObject->width-2;
			}
			if ($height == null) {
				$height = $this->_parentObject->height-3;
			}
		}
		$hole = $this->_layerHole;
		if (!$hole) {
			$hole = $this->_layerHole = $this->getScreenLayer()->addHole($x, $y, $width, $height);
		}
		$this->width = $width;
		$this->height = $height;
		$hole->offsetX = $this->_holeHPosition;
		$hpos = 1;
		foreach($this->_columns as $column) {
			$hole->color(36);
			$hole->setPos($hpos,1);
			$hole->write(str_pad($column->title, $column->width, ' ', $column->align));
			$hole->color(0);
			$hole->write('|');
			$hole->setPos($hpos,2);
			$hole->write(str_repeat('-', $column->width).'+');
			$hpos += $column->width+1; // because there is a separator char between columns;
		}

		$yp = 1;
		foreach($this->_data as $index => $row) {
			if ($index >= $this->_holeVPosition && $index <= $this->_holeVPosition+$height) {
				$strrow = array();
				$hole->setPos(1,2+($yp++));
				if ($index==$this->_selectedIndex) {
					$hole->color('0;30;46');
				}
				foreach($this->_columns as $column) {
					$value = '';
					if (isset($row[$column->name])) {
						$value = $row[$column->name];
					}
					$content = str_pad($value, $column->width, ' ', $column->align);
					$content = substr($content, 0, $column->width);
					$content = str_pad($content, $column->width, ' ', $column->align);
					$hole->write($content . '|');
				}
				$hole->color('0');
			}
		}
	}
}