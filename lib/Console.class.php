<?php

class Console {
    static private $_inPointer = null;
    static private $_outPointer = null;
    static private $_staticCurosorPosX = 0;
    static private $_staticCurosorPosY = 0;
	
	static public function GetDimensions() {
		$res = array();
		preg_match("/rows.([0-9]+);\scolumns\s([0-9]+);/", strtolower(exec('stty -a |grep columns')), $res);
		return array($res[2], $res[1]);
	}
	
    static public function Clear() {
        self::_seq('2J');
    }
    static public function SetPos($x,$y) {
        self::_seq("{$y};{$x}f");
    }
	static public function Read() {
		$i = self::_getInPointer();
		return fread($i,16);
	}
    static public function Write($text, $x=null, $y=null) {
        if ($x !==null&&$y!==null){
            self::SetPos($x, $y);
        }
        self::_out($text);
    }
    static public function SaveCursorPos() {
        self::_seq('s');
    }
    static public function RestoreCursorPos() {
        self::_seq('u');
    }
    static public function Color($code) {
        self::_seq("{$code}m");
    }
    static public function HideCursor() {
        system("tput civis");
    }
    static public function ShowCursor() {
        system("tput cnorm");
    }
    
    static public function SetStaticCursorPos($x, $y) {
        self::$_staticCurosorPosX = $x;
        self::$_staticCurosorPosY = $y;
    }
    static public function ShowCursorStatic() {
        if (self::$_staticCurosorPosX === null || self::$_staticCurosorPosY === null) {
            self::HideCursor();
        } else {
            self::ShowCursor();
            self::Color('5');
            self::SetPos(self::$_staticCurosorPosX, self::$_staticCurosorPosY);
            self::Color('0');
        }
    }
    static private function _seq($seq) {
        self::_out("\e[{$seq}");
    }
    static private function _getInPointer() {
        if (self::$_inPointer===NULL) {
			system("stty -icanon -echo");
            self::$_inPointer = fopen('php://stdin', 'w');
			stream_set_blocking(self::$_inPointer, 0);
        }
        return self::$_inPointer;
    }
    static private function _getOutPointer() {
        if (self::$_outPointer===NULL) {
            self::$_outPointer = fopen('php://stdout', 'w');
        }
        return self::$_outPointer;
    }
    static private function _out($str) {
        fwrite(self::_getOutPointer(), $str);
    }
}