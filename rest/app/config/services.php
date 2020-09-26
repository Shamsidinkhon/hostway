<?php
declare(strict_types=1);

use Phalcon\Cache;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Mvc\View\Simple as View;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Url as UrlResolver;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * Sets the view component
 */
$di->setShared('view', function () {
    $config = $this->getConfig();

    $view = new View();
    $view->setViewsDir($config->application->viewsDir);
    return $view;
});

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();

    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);
    return $url;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    $connection = new $class($params);

    return $connection;
});

/**
 * The Logger component is used to store errors and exceptions
 */
$di->setShared('logger', function () {
    $adapter = new Stream(APP_PATH . '/runtime/logs/hostway_main.log');
    return new Logger(
        'messages',
        [
            'main' => $adapter,
        ]
    );
});

/**
 * The Cache component is used to cache most used data
 */
$di->setShared('cache', function () {
    $serializerFactory = new SerializerFactory();
    $adapterFactory    = new AdapterFactory($serializerFactory);

    $options = [
        'defaultSerializer' => 'Json',
        'lifetime'          => 7200
    ];

    $adapter = $adapterFactory->newInstance('apcu', $options);
    return new Cache($adapter);
});
