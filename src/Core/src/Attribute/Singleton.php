<?php

declare(strict_types=1);

namespace Spiral\Core\Attribute;

/**
 * Mark class as singleton.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Singleton implements Plugin
{
}
