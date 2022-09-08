<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Fixtures\Listener;

use Spiral\Events\Attribute\Listener;
use Spiral\Tests\Events\Fixtures\Event\BarEvent;
use Spiral\Tests\Events\Fixtures\Event\FooEvent;

final class ClassMethodDoubleAttribute
{
    #[Listener(event: FooEvent::class)]
    #[Listener(event: BarEvent::class)]
    public function __invoke(object $event): void
    {
    }
}
