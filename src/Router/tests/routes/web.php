<?php

declare(strict_types=1);

use Spiral\Router\Route;
use Spiral\Router\RouterInterface;
use Spiral\Router\Target\Action;
use Spiral\Tests\Router\Fixtures\TestController;

return function (RouterInterface $router) {
    $router->setRoute('index', new Route('/', new Action(TestController::class, 'index')));
    $router->setRoute('test', new Route('/test', new Action(TestController::class, 'test')));
};
