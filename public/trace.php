<?php

if (!function_exists('fpr')) {

# function pr($label, $var='') { }
# function dpr() { }

function fpr()
{
    static $first = true;

    $filename = BASE_DIR . '/app/logs/trace.log';

    if ($first) {
        $first = false;
        $str = sprintf("%'-30s %s %'-30s\n", '-', date('Y-m-d H:i:s'), '-');
        if (PHP_SAPI != 'cli') {
        #   $str .= "\tHTTP_HOST    = ".$_SERVER['HTTP_HOST']."\n";
        #   $str .= "\tSERVER_NAME  = ".$_SERVER['SERVER_NAME']."\n";
        #   $str .= "\tREQUEST_URI  = ".$_SERVER['REQUEST_URI']."\n";
        #   $str .= "\tQUERY_STRING = ".$_SERVER['QUERY_STRING']."\n";
        #   $str .= "\tUSER_AGENT   = ".$_SERVER['HTTP_USER_AGENT']."\n";
        #   if (isset($_SERVER['HTTP_REFERER'])) {
        #       $str .= "\tHTTP_REFERER = ".$_SERVER['HTTP_REFERER']."\n";
        #   }
        }
        $str .= "\n";
#       unlink($filename);
        error_log($str, 3, $filename);
    }

    $args = func_get_args();
    foreach ($args as $var) {
        $str = trim(var_export($var, true), "'");
        $str = preg_replace("/=> \n(\s+)/", "=> ", $str);
        error_log($str."\n", 3, $filename);
    }
    error_log("\n", 3, $filename);
}

function ftr($msg)
{
    fpr($msg);

    $files = "{>>>\n";
    $trace = debug_backtrace();
    foreach ($trace as $entry) {
        if (isset($entry['file'])) {
            $files .= $entry['file'] .':'. $entry['line'] . "\n";
        }
    }
    $files .= "<<<}";
    fpr($files);
}

function &timer_fetch()
{
	static $timers = [];
	return $timers;
}

function timer_start($name)
{
	$timers = &timer_fetch();
	$timers[$name]['start'] = microtime(true);
}

function timer_end($name)
{
	$timers = &timer_fetch();
	return $name.': '.number_format(microtime(true) - $timers[$name]['start'], 4);
}

}
