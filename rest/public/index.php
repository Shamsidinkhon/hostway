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

    $app->before(function () use ($app) {
        if (
        ($app->request->isPost() || $app->request->isPut())
            && ($app->request->getContentType() != "application/json" || $app->request->getJsonRawBody(true) == null)
        ) {
            $app->response->setStatusCode(400, 'Bad Request');
            $app->response->send();
            $app->stop();
        }
    });

    // Making the correct answer after executing
    $app->after(
        function () use ($app) {
            // Getting the return value of method
            $return = $app->getReturnedValue();

            if (is_array($return)) {
                // Transforming arrays to JSON
                $app->response->setJsonContent($return);
            } elseif (!strlen($return)) {
                // Successful response without any content
                $app->response->setStatusCode(204, 'No Content');
            } else {
                // Unexpected response
                throw new Exception('Bad Response');
            }

            // Sending response to the client
            $app->response->send();
        }
    );


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
