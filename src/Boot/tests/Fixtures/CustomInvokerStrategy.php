<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\BootloadManager\InvokerStrategyInterface;

final class CustomInvokerStrategy implements InvokerStrategyInterface
{
    public function invokeBootloaders(array $classes, array $bootingCallbacks, array $bootedCallbacks): void {}
}
