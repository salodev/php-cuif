<?php

class ConfirmWindow extends Window {
	public function init(array $params = array()) {
		$this->title=isset($params['title'])?$params['title']:'Confirm:';
		$this->createLabelBox(1, 0, $params['text']);
		$this->height=1;
		$this->setToolKeys(array(
			'S, Y, <ENTER>' => 'Confirma',
			'N, <ESCAPE>' => 'Cancela',
		));
		$this->bind('keyPress', function(Input $input) {
			$key = $input->raw; $spec = $input->spec;
			if (in_array(strtoupper($key),['S','Y'])||$spec=='RETURN') {
				$this->trigger('confirm');
			}
			if (strtoupper($key)=='N'||$spec=='ESCAPE') {
				$this->trigger('cancel');
			}
		});
	}
}