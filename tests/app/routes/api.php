<?php

declare(strict_types=1);

use Spiral\Router\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('test-import-index', '/test-import')->callable(static fn () => null);
    $routes->add('test-import-posts', '/test-import/posts')->callable(static fn () => null);
    $routes->add('test-import-post', '/test-import/post/<id>')->callable(static fn () => null);
};
