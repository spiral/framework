<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Introspector;

use Psr\Container\ContainerInterface;
use Spiral\Core\BinderInterface;
use Spiral\Core\Config;
use Spiral\Core\Container as PublicContainer;
use Spiral\Core\FactoryInterface;
use Spiral\Core\Internal;
use Spiral\Core\InvokerInterface;
use Spiral\Core\Options;
use Spiral\Core\ResolverInterface;

/**
 * @internal
 *
 * @property-read Internal\State $state
 * @property-read ResolverInterface|Internal\Resolver $resolver
 * @property-read FactoryInterface|Internal\Factory $factory
 * @property-read ContainerInterface|Internal\Container $container
 * @property-read BinderInterface|Internal\Binder $binder
 * @property-read InvokerInterface|Internal\Invoker $invoker
 * @property-read Internal\Scope $scope
 * @property-read Config $config
 * @property-read Options $options
 */
final class Accessor
{
    public function __construct(
        public PublicContainer $publicContainer,
    ) {
    }

    public function __get(string $name): object
    {
        return (fn (PublicContainer $c): object => $c->$name)->call($this->publicContainer, $this->publicContainer);
    }
}
