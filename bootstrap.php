<?php
set_time_limit(0);
spl_autoload_register(function($className) {
	$className = str_replace('\\','/', $className);
    $classFile = dirname(__FILE__) . "/{$className}.class.php";
    if (file_exists($classFile)) {
        require_once($classFile);
    }
});