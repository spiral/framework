<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Tests\Boot\Fixtures\Attribute\TargetWorker;

#[TargetWorker('http')]
final class BootloaderK extends AbstractBootloader {}
