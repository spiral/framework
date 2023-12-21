<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

use DateTimeInterface;
use Spiral\Core\Container\InjectorInterface;

/**
 * @implements InjectorInterface<DateTimeInterface>
 */
class ExtendedContextInjector implements InjectorInterface
{
    public function createInjection(\ReflectionClass $class, \ReflectionParameter|string|null $context = null): object
    {
        return (object)['context' => $context];
    }
}
