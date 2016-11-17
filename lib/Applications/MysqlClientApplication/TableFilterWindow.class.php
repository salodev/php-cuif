<?php
namespace Applications\MysqlClientApplication;

class TableFilterWindow extends \Window {
	/**
	 *
	 * @var TableWindow 
	 */
	private $_tableWindow;
	public function init(array $params = array()) {
		$this->color = '30;42';
		$this->_tableWindow = $params['tableWindow'];
		$w = $this->_tableWindow;
		$this->x = $w->x + 10;
		$this->y = $w->y + $w->list->getRowInfo()->relativeVPos + 5;
		$this->width = 100;
		$column = $w->list->getCurrentColumn();
		$this->columnName = $column->name;
		$this->title = 'FILTER BY ' . $this->columnName . ' [PRESS <F2> TO CHANGE IT]';
		$value = $w->list->getRowData($column->name);
		$y = 0;
		$tabIndex = 0;
		$filters = $this->filters = $w->getFilters();
		if (count($filters)) {
			$tabIndex++;
			$this->createLabelBox(1, $y++, 'APPLIED FILTERS: [<INS> to ENABLE/DISABLE, <DEL> to REMOVE]');
			$y++;
			$this->height+=2;
		}
		foreach($this->filters as &$filter) {
			$tabIndex++;
			$ib = $this->createInputBox(1, $y++, $filter->columnName . ' ' . $filter->spec, $filter->value);
			$ib->filter = $filter;
			$ib->postLabel = ($filter->enabled?'':'[DISABLED]');
			$this->height++;
		}
		if (count($filters)) {
			$b = $this->createButton(1, $y++, 'CLEAR ALL');
			$tabIndex++;
			$y++;
			$this->height+=2;
			$b->bind('press', function() use ($w) {
				$w->clearFilters();
				$this->close();
				$w->updateList();
			});
		}
		$this->createInputBox(1, $y++, '=        ', $value);
		$this->createInputBox(1, $y++, '<>       ', $value);
		$this->createInputBox(1, $y++, '>=       ', $value);
		$this->createInputBox(1, $y++, '<=       ', $value);
		$this->createInputBox(1, $y++, 'LIKE     ', $value);
		$this->createInputBox(1, $y++, 'NOT LIKE ', $value);
		$this->createInputBox(1, $y++, 'MATCH    ', $value);
		$this->createInputBox(1, $y++, 'NOT MATCH', $value);
		$this->createInputBox(1, $y++, 'IN       ', $value);
		$this->createInputBox(1, $y++, 'NOT IN   ', $value);
		list(,$screenHeight) = \Screen::GetInstance()->getDimensions();
		if ($this->y+$this->height>$screenHeight-3) {
			$this->y = $this->y-$this->height-5;
			if ($this->y <1) {
				$this->y = 1;
			}
		}
		$this->setTabStop($tabIndex);
		$this->render();
		$this->keyPress('RETURN', function () use ($w) {
			$fo = $this->getFocusedObject();
			if (!($fo instanceof \InputBox)) {
				return;
			}
			if ($fo->filter && $fo->filter instanceof TableFilter) {
				$fo->filter->value = $fo->value;
				$w->clearFilters();
				foreach($this->filters as $filter) {
					$w->addFilter($filter->spec, $filter->columnName, $filter->value);
				}
			} else {
				$filterSpec = trim($fo->label);
				$filterValue = trim($fo->value);
				$w->addFilter($filterSpec, $this->columnName, $filterValue);
			}
			$this->close();
			$w->updateList();
		});
		$this->keyPress('INSERT' , function() use ($w) {
			$fo = $this->getFocusedObject();
			if (!($fo instanceof \InputBox)) {
				return;
			}
			if ($fo->filter && $fo->filter instanceof TableFilter) {
				$fo->filter->enabled = !$fo->filter->enabled;
				$w->clearFilters();
				foreach($this->filters as $filter) {
					$w->addFilter($filter->spec, $filter->columnName, $filter->value, $filter->enabled);
				}
				$this->close();
				$w->updateList();
			}
			
		});
		$this->keyPress('DELETE' , function() use ($w) {
			$fo = $this->getFocusedObject();
			if (!($fo instanceof \InputBox)) {
				return;
			}
			if ($fo->filter && $fo->filter instanceof TableFilter) {
				$w->clearFilters();
				foreach($this->filters as $filter) {
					if ($filter !== $fo->filter) {
						$w->addFilter($filter->spec, $filter->columnName, $filter->value, $filter->enabled);
					}
				}
				$this->close();
				$w->updateList();
			}
			
		});
		$this->keyPress('F2', function() use ($w) {
			$wfl = $this->_application->openWindow('Window');
			$wfl->title  = 'SELECT AND PRESS <RETURN>';
			$wfl->height = 16;
			$wfl->y = 7;
			$wfl->x = 58;
			$l = $wfl->createListBox();
			$columns = $w->list->getColumns();
			$l->addColumn('Field Name', 'name', 64);
			foreach($columns as $column) {
				$l->addRow((array)$column);
			}
			$wfl->keyPress('RETURN', function() use($w, $wfl, $l) {
				$columnName = $l->getRowData('name');
				$this->columnName = $columnName;
				$this->title = 'FILTER BY ' . $this->columnName . ' [PRESS <F2> TO CHANGE IT]';
				$wfl->close();
			});
			$wfl->render();
		});
	}
}