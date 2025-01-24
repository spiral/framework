<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Fixtures\Listener;

use Spiral\Events\Attribute\Listener;
use Spiral\Tests\Events\Fixtures\Event\FooEvent;

final class MethodAttributeWithParameters
{
    #[Listener(method: 'customMethod')]
    public function customMethod(FooEvent $event): void {}
}
