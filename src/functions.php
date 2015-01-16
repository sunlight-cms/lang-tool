<?php

namespace SunlightTools\LangTool;

/**
 * Print a status message
 *
 * @param string $msg
 * @param array  $arg1,... sprintf() arguments
 */
function status($msg)
{
    $args = array_slice(func_get_args(), 1);

    echo empty($args) ? $msg : vsprintf($msg, $args), "\n";
}

/**
 * Print an error message and exit
 *
 * @param string $msg
 * @param array  $arg1,... sprintf() arguments
 */
function error()
{
    call_user_func_array(__NAMESPACE__ . '\status', func_get_args());
    exit(1);
}
