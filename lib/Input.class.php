<?php

class Input {
    const KEY_ARROW_UP    = '1b5b41';
    const KEY_ARROW_DOWN  = '1b5b42';
    const KEY_ARROW_RIGHT = '1b5b43';
    const KEY_ARROW_LEFT  = '1b5b44';
    const KEY_INSERT      = '1b5b327e';
    const KEY_DELETE      = '1b5b337e';
    const KEY_HOME        = '1b5b317e';
    const KEY_END         = '1b5b347e';
    const KEY_PAGE_UP     = '1b5b357e';
    const KEY_PAGE_DOWN   = '1b5b367e';
    const KEY_ESCAPE      = '1b';
    const KEY_TAB         = '09';
    const KEY_RETURN      = '0a';
    const KEY_BACKSPACE   = '7f';
    const KEY_SPACE       = '20';
    const KEY_F1          = '1b4f50';
    const KEY_F2          = '1b4f51';
    const KEY_F3          = '1b4f52';
    const KEY_F4          = '1b4f53';
    const KEY_F5          = '1b4f54';
    const KEY_F6          = '1b4f55';
    const KEY_F7          = '1b4f56';
    const KEY_F8          = '1b4f57';
    const KEY_F9          = '1b4f58';
    const KEY_F10         = '1b4f59';
    const KEY_F11         = '1b4f5a';
    const KEY_F12         = '1b4f5b';

	static $specs = array(
		Input::KEY_ARROW_UP    => 'ARROW_UP',
		Input::KEY_ARROW_DOWN  => 'ARROW_DOWN',
		Input::KEY_ARROW_RIGHT => 'ARROW_RIGHT',
		Input::KEY_ARROW_LEFT  => 'ARROW_LEFT',
		Input::KEY_INSERT      => 'INSERT',
		Input::KEY_DELETE      => 'DELETE',
		Input::KEY_HOME        => 'HOME',
		Input::KEY_END         => 'END',
		Input::KEY_PAGE_UP     => 'PAGE_UP',
		Input::KEY_PAGE_DOWN   => 'PAGE_DOWN',
		Input::KEY_ESCAPE      => 'ESCAPE',
		Input::KEY_TAB         => 'TAB',
		Input::KEY_RETURN      => 'RETURN',
		Input::KEY_BACKSPACE   => 'BACKSPACE',
		Input::KEY_SPACE       => 'SPACE',
		Input::KEY_F1          => 'F1',
		Input::KEY_F2          => 'F2',
		Input::KEY_F3          => 'F3',
		Input::KEY_F4          => 'F4',
		Input::KEY_F5          => 'F5',
		Input::KEY_F6          => 'F6',
		Input::KEY_F7          => 'F7',
		Input::KEY_F8          => 'F8',
		Input::KEY_F9          => 'F9',
		Input::KEY_F10         => 'F10',
		Input::KEY_F11         => 'F11',
		Input::KEY_F12         => 'F12',
	);
	
	public $raw  = null;
	public $hex  = null;
	public $spec = null;
	
	public function __construct($raw) {
		$this->raw = $raw;
		$this->hex = bin2hex($raw);
		$this->spec = '';
		if (isset(Input::$specs[$this->hex])) {
			$this->spec = Input::$specs[$this->hex];
		}
	}
}