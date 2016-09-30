<?php

class ListBox extends VisualObject {
	private $_data = array();
	private $_columns = array();
	private $_selectedIndex = 0;
	private $_holeVPosition  = 0;
	private $_holeHPosition  = 0;
	public $height = null;
	public $width  = null;

	public function input($msg, $hexMsg) {

		$height = $this->height;
		if ($this->_parentObject != null) {
			if ($height == null) {
				$height = $this->_parentObject->height-3;
			}
		}
		if ($hexMsg==Input::KEY_ARROW_UP) {
			$this->_selectedIndex--;
		}
		if ($hexMsg==Input::KEY_ARROW_DOWN) {
			$this->_selectedIndex++;
		}
		if ($hexMsg==Input::KEY_ARROW_LEFT) {
			$this->_holeHPosition-=4;
		}
		if ($hexMsg==Input::KEY_ARROW_RIGHT) {
			$this->_holeHPosition+=4;
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
		Console::SetPos($x, $y);
		$strheaders = array();
		foreach($this->_columns as $column) {
			$strheaders[] = str_pad($column->title, $column->width, ' ', $column->align);
		}
		Console::Write(substr(implode('|', $strheaders),$this->_holeHPosition,$width));
		$strheaders = array();
		foreach($this->_columns as $column) {
			$strheaders[] = str_repeat('-', $column->width);
		}
		Console::SetPos($x, ++$y);
		Console::Write(substr(implode('+', $strheaders),$this->_holeHPosition,$width));

		foreach($this->_data as $index => $row) {
			if ($index >= $this->_holeVPosition && $index <= $this->_holeVPosition+$height) {
				$strrow = array();
				foreach($this->_columns as $column) {
					$value = '';
					if (isset($row[$column->name])) {
						$value = $row[$column->name];
					}
					$strrow[] = str_pad($value, $column->width, ' ', $column->align);
				}

				Console::SetPos($x, ++$y);
				if ($index==$this->_selectedIndex) {
					Console::Color('7');
				}
				Console::Write(substr(implode('|', $strrow),$this->_holeHPosition,$width));
				Console::Color('0');
			}
		}
	}
}