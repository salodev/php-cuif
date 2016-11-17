<?php
/**
 * unicode like code page 437 (CP437)
 * http://jonathonhill.net/2012-11-26/box-drawing-in-php/
 * https://en.wikipedia.org/wiki/Code_page_437
 */
class Box {
	static $tableUnicode = [
		'HS' => 'e29480', //─
		'VS' => 'e29482', //│
		'TLS' => 'e2948c', //┌
		'TRS' => 'e29490', //┐
		'BLS' => 'e29494', //└
		'BRS' => 'e29498', //┘
		'VTLSS' => 'e2949c', //├
		'VTRSS' => 'e294a4', //┤
		'HTTSS' => 'e294ac', //┬
		'HTBSS' => 'e294b4', //┴
		'CS' => 'e294bc', //┼
		'HD' => 'e29590', //═
		'VD' => 'e29591', //║
		'TLD' => 'e29594', //╔
		'TRD' => 'e29597', //╗
		'BLD' => 'e2959a', //╚
		'BRD' => 'e2959d', //╝
		'VTLDS' => 'e2959f', //╟
		'VTLDD' => 'e295a0', //╠
		'VTRDS' => 'e295a2', //╢
		'VTRDD' => 'e295a3', //╣
		'HTTDS' => 'e295a4', //╤
		'HTTDD' => 'e295a6', //╦
		'HTBDS' => 'e295a7', //╧
		'HTBDD' => 'e295a9', //╩
		'CD' => 'e295ac', //╬
	];
	static public function Draw() {
		
	}
	static public function Get($spec) {
		if (isset(self::$tableUnicode[$spec])) {
			return hex2bin(self::$tableUnicode[$spec]);
		}
	}
	
	static public function GetSymbols($style = 'double') {
		$symbols = [
			'double' => 'TLD,TRD,BLD,BRD,HD,VD,VTLDS,HS,VTRDS',
			'single' => 'TLS,TRS,BLS,BRS,HS,VS,VTLSS,HS,VTRSS',
		];
		if (!isset($symbols[$style])) {
			throw new Exception('style must be \'single\' or \'double\'');
		}
		$speclist = explode(',',$symbols[$style]);
		$charlist = [];
		foreach($speclist as $spec) {
			$charlist[] = hex2bin(self::$tableUnicode[$spec]);
		}
		return $charlist;
	}
	/*static public function ShowTable() {
		for($a=250;$a<258;$a++) {
			foreach(str_split('0123456789ABCDEF') as $b) {
				$h = "{$a}{$b}";
				echo $h . ' ' . html_entity_decode('&#x' . $h . ';', ENT_NOQUOTES, 'UTF-8') . "\n";
			}
		}
	}*/
}