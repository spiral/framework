<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Core;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Core\Exception\Container\ArgumentException;
use Spiral\Core\Exception\ControllerException;

/**
 * Provides ability to call controllers in IoC scope.
 *
 * Make sure to bind ScopeInterface in your container.
 */
abstract class AbstractCore implements CoreInterface
{
    /** @var ContainerInterface @internal */
    protected $container;

    /** @var ResolverInterface @internal */
    protected $resolver;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        // resolver is usually the container itself
        $this->resolver = $container->get(ResolverInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function callAction(string $controller, string $action, array $parameters = [])
    {
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            throw new ControllerException(
                "Invalid action `{$controller}`->`{$action}`",
                ControllerException::BAD_ACTION,
                $e
            );
        }

        if ($method->isStatic() || !$method->isPublic()) {
            throw new ControllerException(
                "Invalid action `{$controller}`->`{$action}`",
                ControllerException::BAD_ACTION
            );
        }

        try {
            // getting the set of arguments should be sent to requested method
            $args = $this->resolver->resolveArguments($method, $parameters);
        } catch (ArgumentException $e) {
            throw new ControllerException(
                "Missing/invalid parameter '{$e->getParameter()->name}' of  `{$controller}`->`{$action}`",
                ControllerException::BAD_ARGUMENT
            );
        } catch (ContainerExceptionInterface $e) {
            throw new ControllerException(
                $e->getMessage(),
                ControllerException::ERROR,
                $e
            );
        }

        return ContainerScope::runScope($this->container, function () use ($controller, $method, $args) {
            return $method->invokeArgs($this->container->get($controller), $args);
        });
    }
}
