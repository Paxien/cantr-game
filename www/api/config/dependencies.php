<?php
// DIC configuration

/** @var Pimple\Container $container */

use Api\Repositories\AuthenticationRepository;
use Api\Repositories\AuthenticationRepositoryInterface;
use Illuminate\Database\Capsule\Manager;

$container = $app->getContainer();

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));

    return $logger;
};

$container['db'] = function ($container) {
    $capsule = new Manager;
    $capsule->addConnection($container['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container[AuthenticationRepositoryInterface::class] = function ($container) {
    return new AuthenticationRepository($container);
};