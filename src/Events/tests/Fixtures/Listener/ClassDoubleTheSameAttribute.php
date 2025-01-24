<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Fixtures\Listener;

use Spiral\Events\Attribute\Listener;
use Spiral\Tests\Events\Fixtures\Event\BarEvent;
use Spiral\Tests\Events\Fixtures\Event\FooEvent;

#[Listener]
#[Listener]
final class ClassDoubleTheSameAttribute
{
    public function __invoke(FooEvent|BarEvent $event): void {}
}
