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
use Spiral\Tests\Filters\Fixtures\ExternalFilter;

// values not mentioned in schema
class ExternalFilterTest extends BaseTest
{
    public function testExternalValidation(): void
    {
        $filter = $this->getProvider()->createFilter(ExternalFilter::class, new ArrayInput([
            'id' => 'value'
        ]));

        $this->assertFalse($filter->isValid());
        $filter->id = 'value';
        $this->assertTrue($filter->isValid());

        $this->assertSame('value', $filter->id);
    }

    public function testInvalid(): void
    {
        $filter = $this->getProvider()->createFilter(ExternalFilter::class, new ArrayInput([
            'key' => 'value'
        ]));
        $this->assertFalse($filter->isValid());

        $this->assertSame([
            'id' => 'This value is required.'
        ], $filter->getErrors());
    }
}
