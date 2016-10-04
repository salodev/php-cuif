<?php

class ExceptionWindow extends Window {
	public function init($params) {
		$exception = $params['exception'];
		list($w,$h)=Console::GetDimensions();
		$this->title = $exception->getMessage();
		$this->width = 100;
		$this->height = 20;
		$this->x = round(($w - $this->width) / 2);
		$this->y = round(($h - $this->height) / 2);
		$this->list = $this->createObject('ListBox');
		$this->list->addColumn('File', 'file', 45);
		$this->list->addColumn('Line', 'line', 5, 0);
		$this->list->addColumn('Call', 'call', 30);
		$this->list->addColumn('Args', 'args', 15);
		$trace = $exception->getTrace();
		foreach($trace as $row) {
			$call = '';
			if (isset($row['class'])) {
				$call .= $row['class'] . $row['type'];
			}
			$call .= $row['function'] . '()';
			$this->list->addRow(array(
				'file' => (strlen($row['file']) > 45)? '...' . substr($row['file'], -42) : $row['file'],
				'line' => $row['line'],
				'call' => $call,
				'args' => '',
			));
		}
	}
}