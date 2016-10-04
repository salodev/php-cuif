<?php

class Worker {
	static private $_stopped = true;
	static private $_tasks = array();
	static public function Start($usleep = 1) {
		self::$_stopped = false;
		while (true) {
			usleep($usleep);
			foreach(self::$_tasks as $taskIndex => $taskInfo) {
				if (self::$_stopped) {
					break 2;
				}
				$taskInfo[0]();
				if ($taskInfo[1]===true) {
					self::removeTask($taskIndex);
				}
			}
		};
	}
	static public function Stop() {
		self::$_stopped = true;
	}
	static public function AddTask($callback, $removeSiceExec = false) {
		self::$_tasks[] = array($callback, $removeSiceExec);
		end(self::$_tasks);
		return key(self::$_tasks); // returns index id.
	}
	static public function RemoveTask($taskIndex) {
		unset(self::$_tasks[$taskIndex]);
	}
	static public function IsRunning() {
		return !self::$_stopped;
	}
	static public function GetCountTasks() {
		return count(self::$_tasks);
	}
}