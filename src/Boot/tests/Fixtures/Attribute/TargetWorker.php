<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Boot\Attribute\BootloadConfig;

#[\Attribute(\Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class TargetWorker extends BootloadConfig
{
    public function __construct(array|string $workers)
    {
        parent::__construct(allowEnv: ['RR_MODE' => $workers]);
    }
}
