<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

use Spiral\Core\Container\InjectorInterface;
use stdClass;

/**
 * @implements InjectorInterface<stdClass>
 */
class ExtendedContextInjector implements InjectorInterface
{
    public function createInjection(\ReflectionClass $class, \ReflectionParameter|string|null $context = null): object
    {
        return (object)['context' => $context];
    }
}
