<?php

// load functions
require __DIR__ . '/functions.php';


// check environment
if ('cli' !== PHP_SAPI) {
    error('Please run from CLI');
}

// init environment
error_reporting(E_ALL | E_STRICT);
mb_internal_encoding('UTF-8');

// error handler
set_error_handler(function ($code, $message, $file = null, $line = null) {
    if (($code & error_reporting()) !== (0)) {
        throw new \ErrorException($message, 0, $code, $file, $line);
    } else {
        return true;
    }
});

// class loader
spl_autoload_register(function ($class) {
    if (
        strncmp($class, 'SunlightTools\\LangTool\\', 23) === 0
        && is_file($file = __DIR__ . '/' . str_replace('\\', '/', substr($class, 23)) . '.php')
    ) {
        include $file;
    }
});
