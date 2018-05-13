<?php

declare(strict_types=1);

$apiServer = realpath(__DIR__.'/../../../public_html/api/index.php');
$apiLocal = realpath(__DIR__.'/../../../web/api/index.php');

$allowed = [];
$allowed[__FILE__] = ['los_basepath' => ''];
if ($apiServer) $allowed[$apiServer] = ['los_basepath' => '/api'];
if ($apiLocal) $allowed[$apiLocal] = ['los_basepath' => '/api'];
$bootConfig = $allowed[$_SERVER['SCRIPT_FILENAME']];

// Delegate static file requests back to the PHP built-in webserver
if (PHP_SAPI === 'cli-server' && !$bootConfig) {
    return false;
}

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/**
 * Self-called anonymous function that creates its own scope and keep the global namespace clean.
 */
(function () use ($bootConfig) {
    /** @var \Psr\Container\ContainerInterface $container */
    $container = require 'config/container.php';

    /** @var \Zend\Expressive\Application $app */
    $app = $container->get(\Zend\Expressive\Application::class);
    $factory = $container->get(\Zend\Expressive\MiddlewareFactory::class);

    // Execute programmatic/declarative middleware pipeline and routing
    // configuration statements
    (require 'config/pipeline.php')($app, $factory, $container);
    (require 'config/routes.php')($app, $factory, $container);

    $app->run();
})();
