<?php

error_reporting(E_ALL);
ini_set("display_errors", "off");
register_shutdown_function("checkForFatal");
set_error_handler("errorHandler");
set_exception_handler("exceptionHandler");

// error logger
//
function logError($msg)   { trigger_error($msg, E_USER_ERROR); }
function logWarning($msg) { trigger_error($msg, E_USER_WARNING); }
function logNotice($msg)  { trigger_error($msg, E_USER_NOTICE); }

function errorHandler($num, $str, $file, $line, $context = null)
{
    $types = [
        E_USER_ERROR   => "Error: ",
        E_USER_WARNING => "Warning: ",
        E_USER_NOTICE  => "Notice: ",

        E_ERROR        => "ERROR: ",
        E_WARNING      => "WARNING: ",
        E_NOTICE       => "NOTICE: ",
    ];

    $type = "ERROR: ";

    if (isset($types[$num])) {
        $type = $types[$num];
    }

    exceptionHandler(new ErrorException($type.$str, 0, $num, $file, $line));
}

function exceptionHandler($e)
{
    $today = date('Y-m-d');

    $filename = BASE_DIR . "/app/logs/exception-$today.log";

    $message  = date('H:i:s ').$e->getMessage() . EOL;
    $message .= "\t";
    $message .= str_replace('\\', '/', $e->getFile()).':'.$e->getLine().EOL;

#   $message .= "Backtrace:\n";
#   $message .= str_replace('\\', '/', $e->getTraceAsString()).EOL;

    $message .= EOL;

    if (IS_CLI) {
        echo $message;
    }

#   file_put_contents($filename, $message, FILE_APPEND);
    error_log($message, 3, $filename);
}

function checkForFatal()
{
    $error = error_get_last();
    if ($error["type"] == E_ERROR) {
        errorHandler($error["type"], $error["message"], $error["file"], $error["line"]);
    }
}
