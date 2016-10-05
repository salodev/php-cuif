<?php

class CUIF {
	static public function StartApplication($applicationClassName, $keyDebug = false) {
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
		}, true); // will be killed after execution.
		Worker::AddTask(function() use($application, $keyDebug) {
			$input = Console::Read();
			if (strlen($input)) {
				try {
					$application->input($input, bin2hex($input));
				} catch (Exception $e) {
					$application->openWindow('ExceptionWindow', array(
						'exception' => $e,
					));
				}
                Console::SetStaticCursorPos(null, null);
				Console::HideCursor();
                Screen::GetInstance()->refresh();
				if ($keyDebug) {
					Console::Write('Printable Pressed Key: ' . $input,0,1);
					Console::Write('HEX Pressed Key      : ' . bin2hex($input),0,2);
					Console::Write('Count of Layers      : ' . Screen::GetInstance()->getLayersCount(),0,3);
				}
				Console::SetPos(1,1);
                Console::ShowCursorStatic();
			}
		});
		Worker::AddTask(function() {
			if (!(Worker::GetCountTasks()>0)) {
				Worker::Stop();
			}
		});
		$application->main();
		if (!Worker::IsRunning()) {
			Worker::Start();
		}
		system("stty sane;tput rs1");
	}
}