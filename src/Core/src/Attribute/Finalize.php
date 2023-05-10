<?php

declare(strict_types=1);

namespace Spiral\Core\Attribute;

use Spiral\Core\Internal\Factory\Ctx;

/**
 * Define a finalize method for the class.
 *
 * @internal We are testing this feature, it may be changed in the future.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Finalize implements Plugin
{
    public function __construct(
        public string $method,
    ) {
    }
}
