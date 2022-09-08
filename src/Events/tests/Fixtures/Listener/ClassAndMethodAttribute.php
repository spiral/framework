<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Fixtures\Listener;

use Spiral\Events\Attribute\Listener;
use Spiral\Tests\Events\Fixtures\Event\BarEvent;
use Spiral\Tests\Events\Fixtures\Event\FooEvent;

#[Listener(method: 'onFooEvent')]
class ClassAndMethodAttribute
{
    public function onFooEvent(FooEvent $event): void
    {
    }

    public function onFooEventWithTwoArguments(FooEvent $event, string $test): void
    {
    }

    #[Listener(method: 'onBarEvent')]
    public function onBarEvent(BarEvent $event): void
    {
    }
}
