<?php
declare(strict_types=1);

use Phalcon\Di\FactoryDefault;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Mvc\Micro;

error_reporting(E_ALL & ~E_WARNING);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {
    /**
     * The FactoryDefault Dependency Injector automatically registers the services that
     * provide a full stack framework. These default services can be overidden with custom ones.
     */
    $di = new FactoryDefault();

    /**
     * Include Services
     */
    include APP_PATH . '/config/services.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    /**
     * Include composer autoloader
     */
    require APP_PATH . "/../../vendor/autoload.php";

    /**
     * Starting the application
     * Assign service locator to the application
     */
    $app = new Micro($di);

    $app->error(function ($e) {
        $adapter = new Stream(APP_PATH . '/runtime/logs/hostway_main.log');
        $logger = new Logger(
            'messages',
            [
                'main' => $adapter,
            ]
        );
        $logger->critical($e->getMessage() . '<br>' . '<pre>' . $e->getTraceAsString() . '</pre>');
    });

    /**
     * Include Application
     */
    include APP_PATH . '/app.php';

    /**
     * Handle the request
     */
    $app->handle($_SERVER['REQUEST_URI']);
} catch (\Exception $e) {
}
