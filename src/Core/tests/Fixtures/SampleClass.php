<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

class SampleClass
{
    public function nullableScalar(?string $nullable): ?string
    {
        return $nullable;
    }
}
