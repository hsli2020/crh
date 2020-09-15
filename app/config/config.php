<?php

// application
$application = [
    'controllersDir'           => BASE_DIR . '/app/controllers/',
    'modelsDir'                => BASE_DIR . '/app/models/',
    'viewsDir'                 => BASE_DIR . '/app/views/',
    'pluginsDir'               => BASE_DIR . '/app/plugins/',
    'serviceDir'               => BASE_DIR . '/app/services/',
    'libraryDir'               => BASE_DIR . '/app/library/',
    'formsDir'                 => BASE_DIR . '/app/forms/',
    'cacheDir'                 => BASE_DIR . '/app/cache/',
    'voltDir'                  => BASE_DIR . '/app/cache/volt/',
    'logDir'                   => BASE_DIR . '/app/logs/',
    'utilsDir'                 => BASE_DIR . '/app/utils/',
    'securityDir'              => BASE_DIR . '/app/cache/security/',
    'vendorDir'                => BASE_DIR . '/vendor',
    'baseUri'                  => '',
    'appTitle'                 => 'Solar EMS',
    'appName'                  => 'solar-ems',
    'baseUrl'                  => 'https://www.solar-ems.dev',
    'debug'                    => '0',
    'securitySalt'             => 'b5hdr6f9t5a6tjhpei9m',
    'cryptSalt'                => 'eEAfR|_&G&f,+vUx:jFr!!A&+71w1Ms9~8_4L!<@xN@DyaIP_2My|:+.u>/6m,$D',
    'pagination'               => array('itemsPerPage' => 25),
    'hashTokenExpiryHours'     => 4,
    'dateTimeFormat'           => 'Y-m-d H:i:s'
];

// cache
$cache = [
    'lifetime' => 86400,
    'cacheDir' => BASE_DIR . '/app/cache/',
];

// routes
$routes = require_once(__DIR__ . '/routes.php');

// Environment based settings
$database = [
    'host'        => '127.0.0.1',
    'username'    => 'root',
    'password'    => '',
    'dbname'      => 'solar'
];

$logger = [
    'path'     => BASE_DIR . '/app/logs/',
    'filename' => 'app.log',
    'format'   => '%date% [%type%] %message%',
    'date'     => 'Y-m-d H:i:s',
    'logLevel' => Phalcon\Logger::DEBUG,
];

return [
    'application' => $application,
    'database'    => $database,
    'routes'      => $routes,
    'cache'       => $cache,
    'logger'      => $logger,
];
