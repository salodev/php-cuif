<?php

class ConfirmWindow extends Window {
	public function init(array $params = array()) {
		$this->title=$params['title'];
		$this->createLabelBox(1, 0, $params['text']);
		$this->height=1;
		$this->setToolKeys(array(
			'S, Y, <ENTER>' => 'Confirma',
			'N, <ESCAPE>' => 'Cancela',
		));
		$this->bind('keyPress', function($params) {
			list($key,$keyHex) = $params;
			if (in_array(strtoupper($key),['S','Y'])||$keyHex==\Input::KEY_RETURN) {
				$this->trigger('confirm');
			}
			if (strtoupper($key)=='N'||$keyHex==\Input::KEY_ESCAPE) {
				$this->trigger('cancel');
			}
		});
	}
}