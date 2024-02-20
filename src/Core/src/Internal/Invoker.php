<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\Container\NotCallableException;
use Spiral\Core\Internal\Common\DestructorTrait;
use Spiral\Core\Internal\Common\Registry;
use Spiral\Core\InvokerInterface;
use Spiral\Core\Options;
use Spiral\Core\ResolverInterface;

/**
 * @psalm-type TResolver = class-string|non-empty-string|callable|array{class-string, non-empty-string}
 *
 * @internal
 */
final class Invoker implements InvokerInterface
{
    use DestructorTrait;

    private ContainerInterface $container;
    private ResolverInterface $resolver;
    private Options $options;

    public function __construct(Registry $constructor)
    {
        $constructor->set('invoker', $this);

        $this->container = $constructor->get('container', ContainerInterface::class);
        $this->resolver = $constructor->get('resolver', ResolverInterface::class);
        $this->options = $constructor->getOptions();
    }

    /**
     * @psalm-param TResolver $target
     */
    public function invoke(mixed $target, array $parameters = []): mixed
    {
        if (\is_array($target) && isset($target[1])) {
            // In a form of resolver and method
            [$resolver, $method] = $target;

            // Resolver instance (i.e. [ClassName::class, 'method'])
            if (\is_string($resolver)) {
                $resolver = $this->container->get($resolver);
            }

            try {
                $method = new \ReflectionMethod($resolver, $method);
            } catch (\ReflectionException $e) {
                throw new ContainerException($e->getMessage(), $e->getCode(), $e);
            }

            // Invoking factory method with resolved arguments
            return $method->invokeArgs(
                $resolver,
                $this->resolver->resolveArguments($method, $parameters),
            );
        }

        if (\is_string($target) && \is_callable($target)) {
            $target = $target(...);
        }

        if ($target instanceof \Closure) {
            try {
                $reflection = new \ReflectionFunction($target);
            } catch (\ReflectionException $e) {
                throw new ContainerException($e->getMessage(), $e->getCode(), $e);
            }

            // Invoking Closure with resolved arguments
            return $reflection->invokeArgs(
                $this->resolver->resolveArguments($reflection, $parameters, $this->options->validateArguments),
            );
        }

        throw new NotCallableException('Unsupported callable.');
    }
}
