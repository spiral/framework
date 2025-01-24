<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Fixtures\Listener;

use Spiral\Events\Attribute\Listener;
use Spiral\Tests\Events\Fixtures\Event\BarEvent;

#[Listener]
final class ClassAttribute
{
    public function __invoke(BarEvent $event): void {}
}
