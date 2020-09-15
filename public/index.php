<?php

error_reporting(E_ALL);
date_default_timezone_set("America/Toronto");

const EOL = "\n";

define('BASE_DIR', dirname(__DIR__));
#define('APP_DIR', BASE_DIR . '/app');
define('IS_CLI',   PHP_SAPI == 'cli');

include BASE_DIR . '/public/trace.php';
include BASE_DIR . '/public/error.php';
include BASE_DIR . "/app/bootstrap.php";

$bootstrap = new Bootstrap();
echo $bootstrap->run();
