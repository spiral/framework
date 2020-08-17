<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

use Spiral\Core\InjectableConfig;
use Spiral\Core\Traits\Config\AliasTrait;

class TestConfig extends InjectableConfig
{
    use AliasTrait;
}
