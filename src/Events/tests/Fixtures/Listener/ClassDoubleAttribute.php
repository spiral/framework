<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Fixtures\Listener;

use Spiral\Events\Attribute\Listener;
use Spiral\Tests\Events\Fixtures\Event\BarEvent;
use Spiral\Tests\Events\Fixtures\Event\FooEvent;

#[Listener(event: FooEvent::class)]
#[Listener(event: BarEvent::class)]
final class ClassDoubleAttribute
{
    public function __invoke(object $event): void {}
}
