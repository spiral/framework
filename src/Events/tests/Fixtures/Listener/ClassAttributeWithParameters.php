<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Fixtures\Listener;

use Spiral\Events\Attribute\Listener;
use Spiral\Tests\Events\Fixtures\Event\FooEvent;

#[Listener(method: 'customMethod')]
final class ClassAttributeWithParameters
{
    public function customMethod(FooEvent $event): void {}
}
