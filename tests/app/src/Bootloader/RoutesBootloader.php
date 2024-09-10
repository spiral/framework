<?php

declare(strict_types=1);

namespace Spiral\App\Bootloader;

use Spiral\App\Interceptor\Append;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Spiral\App\Controller\AuthController;
use Spiral\App\Controller\InterceptedController;
use Spiral\App\Controller\ScopeController;
use Spiral\App\Controller\TestController;
use Spiral\App\Interceptor;
use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Bootloader\Http\RoutesBootloader as BaseRoutesBootloader;
use Spiral\Cookies\Middleware\CookiesMiddleware;
use Spiral\Core\Core;
use Spiral\Core\InterceptorPipeline;
use Spiral\Csrf\Middleware\CsrfMiddleware;
use Spiral\Debug\StateCollector\HttpCollector;
use Spiral\Domain\PipelineInterceptor;
use Spiral\Http\Middleware\ErrorHandlerMiddleware;
use Spiral\Http\Middleware\JsonPayloadMiddleware;
use Spiral\Router\Loader\Configurator\RoutingConfigurator;
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

    /**
     * @return array<non-empty-string, list<class-string<MiddlewareInterface>>>
     */
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

    protected function defineRoutes(RoutingConfigurator $routes): void
    {
        $routes->add('auth', '/auth/<action>')->controller(AuthController::class);
        $routes->add('scope', '/scope/<action>')->controller(ScopeController::class);
        $routes
            ->add('intercepted:without', '/intercepted/without')
            ->action(InterceptedController::class, 'without')
            ->core($this->getInterceptedCore([
                new Append('one'),
                new Append('two'),
                new Append('three'),
            ]));
        $routes
            ->add('intercepted:with', '/intercepted/with')
            ->action(InterceptedController::class, 'with')
            ->core($this->getInterceptedCore([$this->pipelineInterceptor]));
        $routes
            ->add('intercepted:mix', '/intercepted/mix')
            ->action(InterceptedController::class, 'mix')
            ->core($this->getInterceptedCore([
                new Append('four'),
                new Append('five'),
                $this->pipelineInterceptor,
                new Append('six'),
            ]));
        $routes
            ->add('intercepted:dup', '/intercepted/dup')
            ->action(InterceptedController::class, 'dup')
            ->core($this->getInterceptedCore([
                $this->pipelineInterceptor,
                new Append('one'),
                new Append('two'),
                new Append('three'),
            ]));
        $routes
            ->add('intercepted:skip', '/intercepted/skip')
            ->action(InterceptedController::class, 'skip')
            ->core($this->getInterceptedCore([
                new Append('one'),
                $this->pipelineInterceptor,
                new Append('two'),
                new Append('three'),
            ]));
        $routes
            ->add('intercepted:first', '/intercepted/first')
            ->action(InterceptedController::class, 'first')
            ->core($this->getInterceptedCore([
                $this->pipelineInterceptor,
                new Append('four'),
                new Append('five'),
                new Append('six'),
            ]));
        $routes
            ->add('intercepted:withoutAttribute', '/intercepted/withoutAttribute')
            ->action(InterceptedController::class, 'withoutAttribute')
            ->core($this->getInterceptedCore([
                new Append('one'),
                new Append('two'),
                new Append('three'),
            ]));
        $routes
            ->add('intercepted:withAttribute', '/intercepted/withAttribute')
            ->action(InterceptedController::class, 'withAttribute')
            ->core($this->getInterceptedCore([
                $this->pipelineInterceptor,
            ]));
        $routes
            ->add('intercepted:mixAttribute', '/intercepted/mixAttribute')
            ->action(InterceptedController::class, 'mixAttribute')
            ->core($this->getInterceptedCore([
                new Append('four'),
                new Append('five'),
                $this->pipelineInterceptor,
                new Append('six'),
            ]));
        $routes
            ->add('intercepted:dupAttribute', '/intercepted/dupAttribute')
            ->action(InterceptedController::class, 'dupAttribute')
            ->core($this->getInterceptedCore([
                $this->pipelineInterceptor,
                new Append('one'),
                new Append('two'),
                new Append('three'),
            ]));
        $routes
            ->add('intercepted:skipAttribute', '/intercepted/skipAttribute')
            ->action(InterceptedController::class, 'skipAttribute')
            ->core($this->getInterceptedCore([
                new Append('one'),
                $this->pipelineInterceptor,
                new Append('two'),
                new Append('three'),
            ]));
        $routes
            ->add('intercepted:firstAttribute', '/intercepted/firstAttribute')
            ->action(InterceptedController::class, 'firstAttribute')
            ->core($this->getInterceptedCore([
                $this->pipelineInterceptor,
                new Append('four'),
                new Append('five'),
                new Append('six'),
            ]));

        // with group, prefix, name prefix
        $routes
            ->import(\dirname(__DIR__, 2) . '/routes/api.php')
            ->group('api')
            ->prefix('api')
            ->namePrefix('api.');

        // with group, prefix
        $routes
            ->import(\dirname(__DIR__, 2) . '/routes/api.php')
            ->group('other')
            ->prefix('other');

        $routes
            ->default('/<action>[/<name>]')
            ->controller(TestController::class)
            ->defaults(['name' => 'Dave'])
            ->middleware($this->middlewareGroups()['web']);
    }

    private function getInterceptedCore(array $interceptors): InterceptorPipeline
    {
        $pipeline = (new InterceptorPipeline())->withCore($this->core);

        foreach ($interceptors as $interceptor) {
            $pipeline->addInterceptor(\is_object($interceptor) ? $interceptor : $this->container->get($interceptor));
        }

        return $pipeline;
    }
}
