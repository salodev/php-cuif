<?php

class Console {
    static private $_outPointer = null;
    static public function Clear() {
        self::_seq('2J');
    }
    static public function SetPos($x,$y) {
        self::_seq("{$x};{$y}f");
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
    static private function _seq($seq) {
        self::_out("\e[{$seq}");
    }
    static private function _getOutPointer() {
        if (self::$_outPointer===NULL) {
            self::$_outPointer = STDOUT;
        }
        return self::$_outPointer;
    }
    static private function _out($str) {
        fwrite(self::_getOutPointer(), $str);
    }
}