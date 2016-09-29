<?php

class CUIF {
	static public function StartApplication($applicationClassName) {
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
		Worker::AddTask(function() use($application) {
			$input = Console::Read();
			if (strlen($input)) {
				$application->input($input, bin2hex($input));
                Console::Clear();
                Console::SetStaticCursorPos(null, null);
                $application->render();
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
