<?php

use Spiral\Router\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('index', '/')->callable(static fn () => null);
    $routes->add('posts', '/posts')->callable(static fn () => null);
    $routes->add('post', '/post/<id>')->callable(static fn () => null);
};
