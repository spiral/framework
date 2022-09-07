<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Fixtures\Listener;

use Spiral\Events\Attribute\Listener;
use Spiral\Tests\Events\Fixtures\Event\BarEvent;
use Spiral\Tests\Events\Fixtures\Event\FooEvent;

#[Listener(method: 'onFooEvent')]
final class ClassAndMethodAttribute
{
    public function onFooEvent(FooEvent $event): void
    {
    }

    #[Listener(method: 'onBarEvent')]
    public function onBarEvent(BarEvent $event): void
    {
    }
}
