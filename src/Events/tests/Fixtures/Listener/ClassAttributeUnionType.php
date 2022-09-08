<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Fixtures\Listener;

use Spiral\Events\Attribute\Listener;
use Spiral\Tests\Events\Fixtures\Event\BarEvent;
use Spiral\Tests\Events\Fixtures\Event\FooEvent;

#[Listener]
final class ClassAttributeUnionType
{
    public function __invoke(FooEvent|BarEvent $event): void
    {
    }
}
