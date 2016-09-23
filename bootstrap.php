<?php

spl_autoload_register(function($className) {
    $classFile = dirname(__FILE__) . "/{$className}.class.php";
    if (file_exists($classFile)) {
        require_once($classFile);
    }
});