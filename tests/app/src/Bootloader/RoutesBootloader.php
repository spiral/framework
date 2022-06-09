<?php

declare(strict_types=1);

namespace Spiral\App\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\App\Controller\AuthController;
use Spiral\App\Controller\InterceptedController;
use Spiral\App\Controller\TestController;
use Spiral\App\Interceptor;
use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Bootloader\Http\RoutesBootloader as BaseRoutesBootloader;
use Spiral\Cookies\Middleware\CookiesMiddleware;
use Spiral\Core\Core;
use Spiral\Core\InterceptableCore;
use Spiral\Csrf\Middleware\CsrfMiddleware;
use Spiral\Debug\StateCollector\HttpCollector;
use Spiral\Domain\PipelineInterceptor;
use Spiral\Http\Middleware\ErrorHandlerMiddleware;
use Spiral\Http\Middleware\JsonPayloadMiddleware;
use Spiral\Router\GroupRegistry;
use Spiral\Router\Route;
use Spiral\Router\RouterInterface;
use Spiral\Router\Target\Action;
use Spiral\Router\Target\Controller;
use Spiral\Session\Middleware\SessionMiddleware;

final class RoutesBootloader extends BaseRoutesBootloader
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly PipelineInterceptor $pipelineInterceptor,
        private readonly Core $core
    ) {
    }

    protected function globalMiddleware(): array
    {
        return [
            ErrorHandlerMiddleware::class,
            JsonPayloadMiddleware::class,
            HttpCollector::class
        ];
    }

    protected function middlewareGroups(): array
    {
        return [
            'web' => [
                CookiesMiddleware::class,
                SessionMiddleware::class,
                CsrfMiddleware::class,
                AuthMiddleware::class,
            ]
        ];
    }

    protected function defineRoutes(RouterInterface $router, GroupRegistry $groups): void
    {
        $web = $groups->getGroup('web');

        $web
            ->addRoute('auth', new Route('/auth/<action>', new Controller(AuthController::class)))
            ->addRoute('intercepted:without', $this->createInterceptedRoute('without', [
                new Interceptor\Append('one'),
                new Interceptor\Append('two'),
                new Interceptor\Append('three'),
            ]))
            ->addRoute('intercepted:with', $this->createInterceptedRoute('with', [$this->pipelineInterceptor]))
            ->addRoute('intercepted:mix', $this->createInterceptedRoute('mix', [
                new Interceptor\Append('four'),
                new Interceptor\Append('five'),
                $this->pipelineInterceptor,
                new Interceptor\Append('six'),
            ]))
            ->addRoute('intercepted:dup', $this->createInterceptedRoute('dup', [
                $this->pipelineInterceptor,
                new Interceptor\Append('one'),
                new Interceptor\Append('two'),
                new Interceptor\Append('three'),
            ]))
            ->addRoute('intercepted:skip', $this->createInterceptedRoute('skip', [
                new Interceptor\Append('one'),
                $this->pipelineInterceptor,
                new Interceptor\Append('two'),
                new Interceptor\Append('three'),
            ]))
            ->addRoute('intercepted:first', $this->createInterceptedRoute('first', [
                $this->pipelineInterceptor,
                new Interceptor\Append('four'),
                new Interceptor\Append('five'),
                new Interceptor\Append('six'),
            ]))
            ->addRoute('intercepted:withoutAttribute', $this->createInterceptedRoute('withoutAttribute', [
                new Interceptor\Append('one'),
                new Interceptor\Append('two'),
                new Interceptor\Append('three'),
            ]))
            ->addRoute('intercepted:withAttribute', $this->createInterceptedRoute('withAttribute', [
                $this->pipelineInterceptor,
            ]))
            ->addRoute('intercepted:mixAttribute', $this->createInterceptedRoute('mixAttribute', [
                new Interceptor\Append('four'),
                new Interceptor\Append('five'),
                $this->pipelineInterceptor,
                new Interceptor\Append('six'),
            ]))
            ->addRoute('intercepted:dupAttribute', $this->createInterceptedRoute('dupAttribute', [
                $this->pipelineInterceptor,
                new Interceptor\Append('one'),
                new Interceptor\Append('two'),
                new Interceptor\Append('three'),
            ]))
            ->addRoute('intercepted:skipAttribute', $this->createInterceptedRoute('skipAttribute', [
                new Interceptor\Append('one'),
                $this->pipelineInterceptor,
                new Interceptor\Append('two'),
                new Interceptor\Append('three'),
            ]))
            ->addRoute('intercepted:firstAttribute', $this->createInterceptedRoute('firstAttribute', [
                $this->pipelineInterceptor,
                new Interceptor\Append('four'),
                new Interceptor\Append('five'),
                new Interceptor\Append('six'),
            ]))
        ;

        $default = new Route('/<action>[/<name>]', new Controller(TestController::class));

        $router->setDefault(
            $default
                ->withDefaults(['name' => 'Dave'])
                ->withMiddleware($this->middlewareGroups()['web'])
        );
    }

    private function createInterceptedRoute(string $action, array $interceptors): Route
    {
        $target = new Action(InterceptedController::class, $action);

        return new Route("/intercepted/$action", $target->withCore($this->getInterceptedCore($interceptors)));
    }

    private function getInterceptedCore(array $interceptors): InterceptableCore
    {
        $core = new InterceptableCore($this->core);

        foreach ($interceptors as $interceptor) {
            $core->addInterceptor(\is_object($interceptor) ? $interceptor : $this->container->get($interceptor));
        }

        return $core;
    }
}
