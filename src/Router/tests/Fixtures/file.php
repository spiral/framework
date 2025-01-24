<?php

declare(strict_types=1);

use Spiral\Router\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('index', '/')->callable(static fn() => null);
    $routes->add('posts', '/posts')->callable(static fn() => null);
    $routes->add('post', '/post/<id>')->callable(static fn() => null);
};
