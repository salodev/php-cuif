<?php

class CUIF {
	static private $_firstTimeExecuion = true;
	static private $_fileLog = null;
	static public $keyDebug = false;
	static public function StartApplication($applicationClassName, $keyDebug = false) {
		self::$keyDebug = $keyDebug;
		if (self::$_firstTimeExecuion) {
			register_shutdown_function(function() {
				Console::ShowCursorStatic();
				system("stty sane;stty echo");
			});
			self::$_firstTimeExecuion = false;
		}
		$application = new $applicationClassName;
		if (!$application instanceof Application) {
			throw new Exception('Must be an Application instance');
		}
		Worker::AddTask(function() use($application) {
			Console::Clear();
			Console::HideCursor();
			Console::SetStaticCursorPos(null, null);
			$application->render();
			Console::ShowCursorStatic();
		}, false); // will be killed after execution.
		Worker::AddTask(function() use($application) {
			$raw = Console::Read();
			if (strlen($raw)) {
				$input = new Input($raw);
				$application->input($input);
                Console::SetStaticCursorPos(null, null);
				Console::HideCursor();
                Screen::GetInstance()->refresh(true);
				if (self::$keyDebug) {
					Console::Write('RAW  Pressed Key: ' . $input->raw,  0, 1);
					Console::Write('HEX  Pressed Key: ' . $input->hex,  0, 2);
					Console::Write('SPEC Pressed Key: ' . $input->spec, 0, 3);
					Console::Write('Count of Layers : ' . Screen::GetInstance()->getLayersCount(),0,4);
				}
				Console::SetPos(1,1);
                Console::ShowCursorStatic();
			}
		}, true, 'STANDARD INPUT READER');
		Worker::AddTask(function() {
        	Screen::GetInstance()->refresh(true);
		}, false);
		$application->main();
		if (!Worker::IsRunning()) {
			Worker::Start(1, function(Exception $e, $taskInfo) use ($application) {
				$application->openWindow('ExceptionWindow', array(
					'exception' => $e,
					'taskInfo'  => $taskInfo,
				));
			});
		}
	}
	
	static public function SetFileLog($filePath) {
		self::$_fileLog = $filePath;
	}
	static public function Log($text){
		file_put_contents(self::$_fileLog, $text."\n", FILE_APPEND);
	}
}