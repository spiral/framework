<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation\Fixtures;

use Spiral\Validation\AbstractChecker;

class TestChecker extends AbstractChecker
{
    public function test(): bool
    {
        return false;
    }
}
