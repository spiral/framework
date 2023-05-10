<?php

declare(strict_types=1);

namespace Spiral\Core\Attribute;

use Spiral\Core\Internal\Factory\Ctx;

/**
 * Mark class as singleton.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Singleton implements Plugin
{
}
