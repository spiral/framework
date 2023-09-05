<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerInterface;
use Spiral\Core\Internal\Common\DestructorTrait;
use Spiral\Core\Internal\Common\Registry;
use Spiral\Core\Internal\Config\StateBinder;

/**
 * @internal
 */
final class Binder extends StateBinder
{
    use DestructorTrait;

    private ContainerInterface $container;
    private Scope $scope;

    public function __construct(Registry $constructor)
    {
        $constructor->set('binder', $this);

        $this->container = $constructor->get('container', ContainerInterface::class);
        $this->scope = $constructor->get('scope', Scope::class);

        parent::__construct($constructor->get('state', State::class));
    }

    public function hasInstance(string $alias): bool
    {
        $parent = $this->scope->getParent();
        if ($parent !== null && $parent->hasInstance($alias)) {
            return true;
        }

        if (!$this->container->has($alias)) {
            return false;
        }

        return parent::hasInstance($alias);
    }

    public function destruct(): void
    {
        unset($this->container);
    }
}
