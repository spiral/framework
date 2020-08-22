<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Filters;

use Spiral\Filters\ArrayInput;
use Spiral\Filters\Exception\SchemaException;
use Spiral\Tests\Filters\UserDefined\InvalidFilter;

class MappingTest extends BaseTest
{
    public function testInvalidPath(): void
    {
        $this->expectException(SchemaException::class);
        $filter = $this->getProvider()->createFilter(InvalidFilter::class, new ArrayInput([]));
        $filter->isValid();
    }
}
