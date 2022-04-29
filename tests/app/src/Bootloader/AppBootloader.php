<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\App\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\App\Checker\MyChecker;
use Spiral\App\Condition\MyCondition;
use Spiral\App\Controller\AuthController;
use Spiral\App\Controller\InterceptedController;
use Spiral\App\Controller\TestController;
use Spiral\App\Interceptor;
use Spiral\App\ViewEngine\TestEngine;
use Spiral\Bootloader\DomainBootloader;
use Spiral\Bootloader\Http\JsonPayloadsBootloader;
use Spiral\Core\Core;
use Spiral\Core\CoreInterface;
use Spiral\Core\InterceptableCore;
use Spiral\Domain\FilterInterceptor;
use Spiral\Domain\GuardInterceptor;
use Spiral\Domain\PipelineInterceptor;
use Spiral\Router\Route;
use Spiral\Router\RouterInterface;
use Spiral\Router\Target\Action;
use Spiral\Router\Target\Controller;
use Spiral\Security\PermissionsInterface;
use Spiral\Validation\Bootloader\ValidationBootloader;
use Spiral\Views\Bootloader\ViewsBootloader;

class AppBootloader extends DomainBootloader
{
    protected const SINGLETONS = [
        CoreInterface::class => [self::class, 'domainCore']
    ];

    protected const INTERCEPTORS = [
        GuardInterceptor::class,
        FilterInterceptor::class
    ];
    /** @var ContainerInterface */
    private $container;

    /** @var Core */
    private $core;

    public function __construct(ContainerInterface $container, Core $core)
    {
        $this->container = $container;
        $this->core = $core;
    }

    public function init(
        RouterInterface $router,
        PermissionsInterface $rbac,
        ViewsBootloader $views,
        ValidationBootloader $validation,
        JsonPayloadsBootloader $json,
        PipelineInterceptor $pipelineInterceptor
    ): void {
        $rbac->addRole('user');
        $rbac->associate('user', '*');

        $rbac->addRole('demo');
        $rbac->associate('demo', 'demo.*');

        $route = new Route(
            '/<action>[/<name>]',
            new Controller(TestController::class)
        );

        $router->setDefault($route->withDefaults(['name' => 'Dave']));

        $router->setRoute(
            'auth',
            new Route('/auth/<action>', new Controller(AuthController::class))
        );

        $views->addDirectory('custom', __DIR__ . '/../../views/custom/');
        $views->addDirectory('stempler', __DIR__ . '/../../views/stempler/');
        $views->addEngine(TestEngine::class);

        $validation->addAlias('aliased', 'notEmpty');
        $validation->addChecker('my', MyChecker::class);
        $validation->addCondition('cond', MyCondition::class);

        $json->addContentType('application/vnd.api+json');

        $this->registerInterceptedRoute(
            $router,
            'without',
            [
                new Interceptor\Append('one'),
                new Interceptor\Append('two'),
                new Interceptor\Append('three'),
            ]
        );

        $this->registerInterceptedRoute(
            $router,
            'with',
            [
                $pipelineInterceptor,
            ]
        );
        $this->registerInterceptedRoute(
            $router,
            'mix',
            [
                new Interceptor\Append('four'),
                new Interceptor\Append('five'),
                $pipelineInterceptor,
                new Interceptor\Append('six'),
            ]
        );
        $this->registerInterceptedRoute(
            $router,
            'dup',
            [
                $pipelineInterceptor,
                new Interceptor\Append('one'),
                new Interceptor\Append('two'),
                new Interceptor\Append('three'),
            ]
        );
        $this->registerInterceptedRoute(
            $router,
            'skip',
            [
                new Interceptor\Append('one'),
                $pipelineInterceptor,
                new Interceptor\Append('two'),
                new Interceptor\Append('three'),
            ]
        );
        $this->registerInterceptedRoute(
            $router,
            'first',
            [
                $pipelineInterceptor,
                new Interceptor\Append('four'),
                new Interceptor\Append('five'),
                new Interceptor\Append('six'),
            ]
        );

        $this->registerInterceptedRoute(
            $router,
            'withoutAttribute',
            [
                new Interceptor\Append('one'),
                new Interceptor\Append('two'),
                new Interceptor\Append('three'),
            ]
        );

        $this->registerInterceptedRoute(
            $router,
            'withAttribute',
            [
                $pipelineInterceptor,
            ]
        );
        $this->registerInterceptedRoute(
            $router,
            'mixAttribute',
            [
                new Interceptor\Append('four'),
                new Interceptor\Append('five'),
                $pipelineInterceptor,
                new Interceptor\Append('six'),
            ]
        );
        $this->registerInterceptedRoute(
            $router,
            'dupAttribute',
            [
                $pipelineInterceptor,
                new Interceptor\Append('one'),
                new Interceptor\Append('two'),
                new Interceptor\Append('three'),
            ]
        );
        $this->registerInterceptedRoute(
            $router,
            'skipAttribute',
            [
                new Interceptor\Append('one'),
                $pipelineInterceptor,
                new Interceptor\Append('two'),
                new Interceptor\Append('three'),
            ]
        );
        $this->registerInterceptedRoute(
            $router,
            'firstAttribute',
            [
                $pipelineInterceptor,
                new Interceptor\Append('four'),
                new Interceptor\Append('five'),
                new Interceptor\Append('six'),
            ]
        );
    }

    private function registerInterceptedRoute(RouterInterface $router, string $action, array $interceptors): void
    {
        $target = new Action(InterceptedController::class, $action);
        $router->setRoute(
            "intercepted:$action",
            new Route(
                "/intercepted/$action",
                $target->withCore($this->getInterceptedCore($interceptors))
            )
        );
    }

    private function getInterceptedCore(array $interceptors = []): InterceptableCore
    {
        $core = new InterceptableCore($this->core);

        foreach ($interceptors as $interceptor) {
            $core->addInterceptor(is_object($interceptor) ? $interceptor : $this->container->get($interceptor));
        }

        return $core;
    }
}
