<?php

spl_autoload_register(function ($class) {
	$e = explode('\\',$class);
	$plugin    = array_shift($e);
	$subplugin = array_shift($e);
	$class     = implode("\\",$e);
    $file = str_replace(array('/', '\\', '//'), DIRECTORY_SEPARATOR, __DIR__ . "/$plugin/$subplugin/classes/$class.php");
    if (file_exists($file)) {
        require_once($file);
        return;
    }
});
