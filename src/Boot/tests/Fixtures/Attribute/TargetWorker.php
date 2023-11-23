<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures\Attribute;

use Spiral\Boot\Attribute\BootloaderRules;
use Spiral\Attributes\NamedArgumentConstructor;

#[\Attribute(\Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class TargetWorker extends BootloaderRules
{
    public function __construct(array|string $workers)
    {
        parent::__construct(allowEnv: ['RR_MODE' => $workers]);
    }
}
