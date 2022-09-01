<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Spiral\Core\Container\InjectorInterface;

/**
 * @implements InjectorInterface<DateTimeInterface>
 */
class DatetimeInjector implements InjectorInterface
{
    public function createInjection(\ReflectionClass $class, string $context = null): object
    {
        return match ($class->getName()) {
            DateTime::class => new DateTime(),
            default => new DateTimeImmutable(),
        };
    }
}
