<?php

use Phalcon\Config;
use Phalcon\DiInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Logger\Formatter\Line as FormatterLine;

use App\Plugins\NotFoundPlugin;
use UserPlugin\Plugin\Security as SecurityPlugin;

class Bootstrap
{
    private $di;
    private $app;

    private $loaders = [
        'cache',
        'session',
        'view',
        'database',
        'router',
        'url',
        'dispatcher',
        'crypt',
        'flash',
        'timezones',
        'breadcrumbs',
        'modelsmetadata',
        'utils',
        'services',
        'security',
        'auth',
        'mail',
        'acl',
    ];

    public function __construct()
    {
        $this->di = new Phalcon\Di\FactoryDefault;

        $em = new EventsManager;
        $em->enablePriorities(true);

        $config = $this->initConfig();

        $this->initLogger($config, $em);
        $this->initLoader($config, $em);

        foreach ($this->loaders as $service) {
            $serviceName = 'init' . ucfirst($service);
            $this->{$serviceName}($config, $em);
        }

        $this->app = new Phalcon\Mvc\Application();

        $this->di->setShared('eventsManager', $em);
        $this->di->setShared('app', $this->app);

        $this->app->setEventsManager($em);
        $this->app->setDI($this->di);
    }

    public function run()
    {
        try {
            return $this->app->handle()->getContent();
        } catch (Exception $e) {
            echo $e->getMessage(), '<br>';
            echo nl2br(htmlentities($e->getTraceAsString()));
        }
    }

    // Protected functions

    protected function initConfig()
    {
        $array = require(BASE_DIR . '/app/config/config.php');

        $config = new Phalcon\Config($array);
        $this->di->setShared('config', $config);

        return $config;
    }

    protected function initLoader(Config $config, EventsManager $em)
    {
        // Creates the autoloader
        $loader = new Phalcon\Loader();

        #$loader->registerDirs([
        #    $config->application->controllersDir,
        #    $config->application->modelsDir,
        #    $config->application->pluginsDir
        #]);

        $loader->registerNamespaces([
            'App\Models'      => $config->application->modelsDir,
            'App\Controllers' => $config->application->controllersDir,
            'App\Forms'       => $config->application->formsDir,
            'App\Plugins'     => $config->application->pluginsDir,
            'App\Service'     => $config->application->serviceDir,
            'App\Library'     => $config->application->libraryDir,
            'App\System'      => BASE_DIR . '/app/system/',
            'App\Entity'      => BASE_DIR . '/app/entity/',
            'App\Repository'  => BASE_DIR . '/app/repository/',
            'App\Interface'   => BASE_DIR . '/app/interface/',
        ]);

        $loader->register();

        // Composer Autoloading
        require_once $config->application->vendorDir . '/autoload.php';

        $this->di->setShared('loader', $loader);
    }

    protected function initLogger(Config $config, EventsManager $em)
    {
        $this->di->setShared('logger', function () use ($config) {
            $logger = new Phalcon\Logger\Adapter\File($config->application->logDir . "/app.log");
            return $logger;
        });

        //$this->di->setShared('logger', function ($filename = null, $format = null) use ($config) {
        //    $format   = $format ?: $config->get('logger')->format;
        //    $filename = trim($filename ?: $config->get('logger')->filename, '\\/');
        //    $path     = rtrim($config->get('logger')->path, '\\/') . DIRECTORY_SEPARATOR;

        //    $formatter = new FormatterLine($format, $config->get('logger')->date);
        //    $logger    = new FileLogger($path . $filename);

        //    $logger->setFormatter($formatter);
        //    $logger->setLogLevel($config->get('logger')->logLevel);

        //    return $logger;
        //});
    }

    protected function initRouter(Config $config, EventsManager $em)
    {
        $this->di->setShared('router', function () use ($config) {
            // Create the router without default routes (false)
            $router = new Phalcon\Mvc\Router(true);
            $router->removeExtraSlashes(true);

            // 404
            #$router->notFound(["controller" => "errors", "action" => "show404"]);

            foreach ($config['routes'] as $route => $items) {
                $router->add($route, $items->params->toArray())->setName($items->name);
            }

            // dash in url: /user/change-password
            $router->add('/([a-zA-Z\-]+)/([a-zA-Z\-]+)/:params', array(
                'controller' => 1,
                'action' => 2,
                'params' => 3
            ))->convert('action', function($action) {
                return Phalcon\Text::lower(Phalcon\Text::camelize($action));
            });

            return $router;
        });
    }

    protected function initDispatcher(Config $config, EventsManager $em)
    {
        $di = $this->di;

        $this->di->setShared('dispatcher', function() use ($em, $di) {

            $em->attach('dispatch:beforeException', new NotFoundPlugin);
#           $em->attach('dispatch:beforeDispatch',  new SecurityPlugin($di));

            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            $dispatcher->setDefaultNamespace('App\Controllers');
            $dispatcher->setEventsManager($em);

            return $dispatcher;
        });
    }

    protected function initSession(Config $config, EventsManager $em)
    {
        $this->di->setShared('session', function () use ($config) {
            // save session in seperated folder to avoid conflicat with another site on same host
            session_save_path($config->application->sessionDir);

            $session = new Phalcon\Session\Adapter\Files(array(
                'uniqueId' => $config->application->appName
            ));

            $session->start();
            return $session;
        });
    }

    /**
     * Initializes the URL component
     * The URL component is used to generate all kind of urls in the application
     */
    protected function initUrl(Config $config, EventsManager $em)
    {
        $this->di->setShared('url', function () use ($config) {
            $url = new Phalcon\Mvc\Url();
            $url->setBaseUri($config->application->baseUri);
            return $url;
        });
    }

    protected function initDatabase(Config $config, EventsManager $em)
    {
        // setup database service
        $this->di->setShared('db', function () use ($config, $em) {
            $connection = new Phalcon\Db\Adapter\Pdo\Mysql([
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->dbname,
            ]);

            Phalcon\Mvc\Model::setup(['notNullValidations' => false]);

            // log sql statements
            if (1 == $config->application->debug) {
                $logger = new Phalcon\Logger\Adapter\File($config->application->logDir . "/db.log");

                $logger->setFormatter(
                    new Phalcon\Logger\Formatter\Line(
                        $config->get('logger')->format,
                        $config->get('logger')->date
                    )
                );
                //$logger->setLogLevel($config->get('logger')->logLevel);

                // Listen all the database events
                $em->attach('db', function ($event, $connection) use ($logger) {
                    if ($event->getType() == 'beforeQuery') {
                        $logger->log($connection->getSQLStatement(), \Phalcon\Logger::INFO);
                    }
                });

                // Assign the eventsManager to the db adapter instance
                $connection->setEventsManager($em);
            }

            return $connection;
        });
    }

    /**
     * Initializes the models metadata
     */
    protected function initModelsMetadata(Config $config, EventsManager $em)
    {
        //$this->di->setShared('modelsMetadata', function() {
        //    return new Phalcon\Mvc\Model\Metadata\Memory();
        //});

        $this->di->setShared('modelsMetadata', function () use ($config) {
            return new Phalcon\Mvc\Model\Metadata\Files([
                'metaDataDir' => $config->application->cacheDir . 'metaData/'
            ]);
        });
    }

    /**
     * Initializes the view and Volt
     */
    protected function initView(Config $config, EventsManager $em)
    {
        $this->di->setShared('view', function () use ($config) {
            $view = new Phalcon\Mvc\View();
            $view->setViewsDir($config->application->viewsDir);
            $view->registerEngines([
                '.volt' => function ($view , $di) use ($config) {
                    $volt = new Phalcon\Mvc\View\Engine\Volt($view , $di);
                    $voltOptions = array(
                        'compiledPath' => $config->application->voltDir,
                        'compiledPath' => function($templatePath) use ($config) {
                            return $config->application->voltDir . md5($templatePath) . '.php';
                        },
                        'compiledSeparator' => '_',
                    );

                    if (1 == $config->application->debug) {
                        $voltOptions['compileAlways'] = true;
                    }

                    $volt->setOptions($voltOptions);

                    //$volt->getCompiler()->addFunction('tr', function ($key) {
                    //    return "Bootstrap::translate({$key})";
                    //});

                    return $volt;
                },
                '.phtml' => 'Phalcon\Mvc\View\Engine\Php', // Generate Template files uses PHP itself as the template engine
            ]);

            return $view;
        });

        $this->di->setShared('simpleView', function () use ($config) {
            $view = new Phalcon\Mvc\View\Simple();
            $view->setViewsDir($config->application->viewsDir);
            $view->registerEngines(['.volt' => 'Phalcon\Mvc\View\Engine\Volt']);

            //$view->disableLevel([
            //	View::LEVEL_LAYOUT      => true,
            //	View::LEVEL_MAIN_LAYOUT => true
            //]);

            return $view;
        });
    }

    protected function initCache(Config $config, EventsManager $em)
    {
        $this->di->setShared('viewCache', function () use ($config) {
            // Get the parameters
            $frontCache = new Phalcon\Cache\Frontend\Output(['lifetime' => $config->cache->lifetime]);

            if (function_exists('apc_store')) {
                $cache = new Phalcon\Cache\Backend\Apc($frontCache);
            } else {
                $backEndOptions = array('cacheDir' => $config->cache->cacheDir);
                $cache = new Phalcon\Cache\Backend\File($frontCache, $backEndOptions);
            }

            return $cache;
        });
    }

    protected function initCrypt(Config $config, EventsManager $em)
    {
        $this->di->setShared('crypt', function () use ($config) {
            $crypt = new Phalcon\Crypt();
            $crypt->setKey($config->application->cryptSalt);
            return $crypt;
        });
    }

    /**
     * Initializes the Flash service
     * Flash service with custom CSS classes
     */
    protected function initFlash(Config $config, EventsManager $em)
    {
        $this->di->setShared('flash', function () {
            return new Phalcon\Flash\Direct([
                'error'   => '', // 'alert alert-danger fade in',
                'success' => '', // 'alert alert-success fade in',
                'notice'  => '', // 'alert alert-info fade in',
                'warning' => '', // 'alert alert-warning fade in',
            ]);
        });

        $this->di->setShared('flashSession', function () {
            return new Phalcon\Flash\Session([
                'error'   => '', // 'alert alert-danger fade in',
                'success' => '', // 'alert alert-success fade in',
                'notice'  => '', // 'alert alert-info fade in',
                'warning' => '', // 'alert alert-warning fade in',
            ]);
        });
    }

    /**
     * Initializes the utilities
     */
    protected function initUtils(Config $config, EventsManager $em)
    {
        // get all the files in app/utils directory
        if ($handle = opendir($config->application->utilsDir)) {

            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    include_once $config->application->utilsDir . "/{$entry}";
                }
            }

            closedir($handle);
        }
    }

    protected function initDebug(Config $config, EventsManager $em)
    {
        $this->di->setShared('debug', function () use ($config) {
            $debug = new Phalcon\Debug();
            if (1 == $config->application->debug) {
                $debug->listen();
            }
            return $debug;
        });
    }

    protected function initTimezones(Config $config, EventsManager $em)
    {
        $this->di->setShared('timezones', function () use ($config) {
            return require_once BASE_DIR . 'app/config/timezones.php';
        });
    }

    protected function initBreadcrumbs(Config $config, EventsManager $em)
    {
        $this->di->setShared('breadcrumbs', function () use ($em) {
            $breadcrumbs = new Phalcon\Breadcrumbs;
            $breadcrumbs->setEventsManager($em);
            $breadcrumbs->setSeparator('');

            return $breadcrumbs;
        });
    }

    /**
     * Initializes the user-defined services, Custom authentication component
     */
    protected function initServices(Config $config, EventsManager $em)
    {
        $this->di->setShared('dataService', function () {
            return new App\Service\DataService();
        });

        $this->di->setShared('userService', function () {
            return new App\Service\UserService();
        });

        $this->di->setShared('importService', function () {
            return new App\Service\ImportService();
        });

        $this->di->setShared('exportService', function () {
            return new App\Service\ExportService();
        });

        $this->di->setShared('alertService', function () {
            return new App\Service\AlertService();
        });
    }

    protected function initSecurity(Config $config, EventsManager $em)
    {
        $this->di->setShared('security', function () {
            $security = new Phalcon\Security();

            // Set the password hashing factor to 12 rounds
            $security->setWorkFactor(12);

            return $security;
        });
    }

    /**
     * Initializes the Auth service, Custom authentication component
     */
    protected function initAuth(Config $config, EventsManager $em)
    {
        $this->di->setShared('auth', function () {
            return new UserPlugin\Auth\Auth();
        });
    }

    /**
     * Initializes the Mail service, Mail service uses AmazonSES
     */
    protected function initMail(Config $config, EventsManager $em)
    {
        $this->di->setShared('mail', function () {
            return new UserPlugin\Mail\Mail();
        });
    }

    /**
     * Initializes the ACL service
     */
    protected function initAcl(Config $config, EventsManager $em)
    {
        $this->di->setShared('acl', function () {
            return new UserPlugin\Acl\Acl();
        });
    }
}
