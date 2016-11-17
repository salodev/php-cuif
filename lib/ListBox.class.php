<?php

class ListBox extends VisualObject {
	private $_data                = array();
	private $_columns             = array();
	private $_selectedIndex       = 0;
	private $_columnIndex         = 0;
	private $_holeVPosition       = 0;
	private $_holeHPosition       = 0;
	private $_headerHole          = null;
	private $_bodyHole            = null;
	private $_columnSelectionMode = false;
	public $height                = null;
	public $width                 = null;
	
	public function __set_x($x) {
		list($xAbs) = $this->getAbsolutePosition();
		$x += $xAbs;
		$this->_x = $x;
		$this->_headerHole->x = $x;
		$this->_bodyHole->x = $x;
	}
	
	public function __set_y($y) {
		list(,$yAbs) = $this->getAbsolutePosition();
		$diff = $y - $this->_y;
		$y += $yAbs;
		$this->_y = $y;
		$this->_headerHole->y = $y;
		$this->_bodyHole->y = $y+2;
		$this->_bodyHole->height -=$diff;
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
	
	public function __get_selectedIndex() {
		return $this->_selectedIndex;
	}
	
	public function __get_columnIndex() {
		return $this->_columnIndex;
	}
	
	protected function _construct(array $params = array()) {
		list($x, $y) = $this->getAbsolutePosition();
		list($width, $height) = $this->_parentObject->getInnerDimensions();
		$this->_headerHole = $this->getScreenLayer()->addHole($x, $y, $width, 2);
		$this->_bodyHole   = $this->getScreenLayer()->addHole($x, $y+2, $width, $height-2);
	}
	
	public function setColumnSelectionMode(){
		$this->_columnSelectionMode = true;
		$this->moveColumnSelection(1);
	}

	public function input(Input $input) {
		list(,$height) = $this->getDimensions();
		if ($input->spec=='ARROW_UP') {
			$this->moveSelection(-1, false);
		}
		if ($input->spec=='ARROW_DOWN') {
			$this->moveSelection(1, false);
		}
		if ($input->spec=='ARROW_LEFT') {
			if ($this->_columnSelectionMode == true) {
				$this->moveColumnSelection(-1, false);
			} else {
				$this->scrollHorizontal(-4, false);
			}
		}
		if ($input->spec=='ARROW_RIGHT') {
			if ($this->_columnSelectionMode == true) {
				$this->moveColumnSelection(1, false);
			} else {
				$this->scrollHorizontal(4, false);
			}
		}
		if ($input->spec=='PAGE_UP') {
			$this->moveSelection($height*-1, false);
		}
		if ($input->spec=='PAGE_DOWN') {
			$this->moveSelection($height, false);
		}
		if ($input->spec=='HOME') {
			if ($this->_selectedIndex==0){
				if ($this->_columnSelectionMode == true) {
					$this->moveColumnSelection(0, true);
				} else {
					$this->scrollHorizontal(0, true);
				}
			}
			$this->moveSelection(0);
		}
		if ($input->spec=='END') {
			$this->moveSelection(count($this->_data)-1);
		}
		$this->trigger('keyPress', $input);
	}
	
	public function moveColumnSelection($index = 0, $absolute = true) {
		if ($absolute) {
			if (!isset($this->_columns[$index])) {
				return;
			}
			$this->_columnIndex = $index;
		} else {
			if (!isset($this->_columns[$this->_columnIndex + $index])) {
				return;
			}
			$this->_columnIndex += $index;
		}
		
		if ($this->_columnIndex > count($this->_columns)-1) {
			$this->_columnIndex = count($this->_columns)-1;
		}
		if ($this->_columnIndex<0) {
			$this->_columnIndex=0;
		}
		$column = $this->_columns[$this->_columnIndex];
		list($width) = $this->getDimensions();
		if ($column->ends - $width > $this->_holeHPosition) {
			$this->scrollHorizontal($column->ends - $width + 5);
		}
		if ($column->starts<$this->_holeHPosition) {
			$this->scrollHorizontal($column->starts);
		}
		$this->_paintSelectedLine();
		$this->_paintSelectedColumn();
	}
	
	private function _paintSelectedLine() {
		$this->_bodyHole->setLineColor('0;30;46', $this->_selectedIndex+1);
	}
	
	private function _paintSelectedColumn() {
		if (!$this->_columnSelectionMode) {
			return;
		}
		if (!isset($this->_columns[$this->_columnIndex])) {
			return;
		}
		$columnOffset = 0;
		$columnWidth  = $this->_columns[$this->_columnIndex]->width;
		if ($this->_columnIndex>0) {
			foreach($this->_columns as $k => $column) {
				if ($k>=$this->_columnIndex) {
					break;
				}
				$columnOffset+=$column->width+1;
			}
		}
		
		for ($x=0;$x<$columnWidth;$x++) {
			$this->_bodyHole->setColor('0;31;47', $x+$columnOffset+1, $this->_selectedIndex+1);
		}
		Screen::GetInstance()->changed();
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
		if ($this->_selectedIndex > $this->_holeVPosition + $this->_bodyHole->height-1) {
			$this->_holeVPosition = $this->_selectedIndex - $this->_bodyHole->height+1;
		}
		$this->_paintSelectedLine();
		$this->_paintSelectedColumn();
		$this->_bodyHole->offsetY = $this->_holeVPosition;
		Screen::GetInstance()->changed();
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
		Screen::GetInstance()->changed();
	}

	public function addColumn($title, $name, $width, $align = 1, $visible = true) {
		$this->_columns[] = (object) array(
			'title'   => $title,
			'name'    => $name,
			'width'   => $width,
			'align'   => $align,
			'visible' => $visible,
			'starts'  => 0,
			'ends'    => $width,
		);
		end($this->_columns);
		$i = key($this->_columns);
		if (prev($this->_columns)!==false) {
			$iprev = key($this->_columns);
			$prevCol = $this->_columns[$iprev];
			$this->_columns[$i]->starts = $prevCol->ends+1;
			$this->_columns[$i]->ends   = $prevCol->ends+1+$width;
		};
	}
	public function addRow(array $data) {
		$this->_data[] = $data;
	}
	public function setData(array $data) {
		$this->_data = $data;
		$this->_bodyHole->clear();
		$this->moveSelection(0);
		$this->render();
		Screen::GetInstance()->refresh(true);
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
	
	public function getRowData($columnName = null) {
		if ($columnName !==null) {
			return $this->_data[$this->_selectedIndex][$columnName];
		}
		return $this->_data[$this->_selectedIndex];
	}
	
	public function getRowInfo() {
		return (object) array(
			'index' => $this->_selectedIndex,
			'relativeVPos' => $this->_selectedIndex - $this->_holeVPosition,
			'columnIndex' => $this->_columnIndex,
			'column' => $this->getCurrentColumn(),
		);
	}
	
	public function getCurrentColumn() {
		return $this->_columns[$this->_columnIndex];
	}
	
	public function getColumns() {
		return $this->_columns;
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
		$hLine = Box::Get('HS');
		$vLine = Box::Get('VS');
		$cross = Box::Get('CS');
		foreach($this->_columns as $column) {
			$this->_headerHole->color(36);
			$this->_headerHole->setPos($hpos,1);
			$this->_headerHole->write(str_pad($column->title, $column->width, ' ', $column->align));
			$this->_headerHole->color(0);
			$this->_headerHole->write(Output::Get('v'), null, null, 3);
			$this->_headerHole->setPos($hpos,2);
			$this->_headerHole->write(str_repeat($hLine, $column->width) . $cross, null, null, 3);
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
				$this->_bodyHole->write($content);
				$this->_bodyHole->write($vLine);
			}
			$this->_bodyHole->color('0');
		}

		$this->_paintSelectedLine();
		$this->_paintSelectedColumn();
	}
}