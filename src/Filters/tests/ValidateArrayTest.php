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
use Spiral\Tests\Filters\Fixtures\ValidateArrayFilter;

class ValidateArrayTest extends BaseTest
{
    public function testValid(): void
    {
        $filter = $this->getProvider()->createFilter(ValidateArrayFilter::class, new ArrayInput([
            'tests' => [
                ['id' => 'value'],
                ['id' => 'value2'],
            ]
        ]));

        $this->assertTrue($filter->isValid());

        $this->assertSame('value', $filter->tests[0]->id);
        $this->assertSame('value2', $filter->tests[1]->id);
    }

    public function testInvalid(): void
    {
        $filter = $this->getProvider()->createFilter(ValidateArrayFilter::class, new ArrayInput([
            'tests' => [
                ['id' => 'value'],
                ['id' => null],
            ]
        ]));

        $this->assertFalse($filter->isValid());

        $this->assertSame('value', $filter->tests[0]->id);
        $this->assertNull($filter->tests[1]->id);

        $this->assertSame([
            'tests' => [
                1 => [
                    'id' => 'This value is required.'
                ]
            ]
        ], $filter->getErrors());
    }

    public function testEmpty(): void
    {
        $filter = $this->getProvider()->createFilter(ValidateArrayFilter::class, new ArrayInput([
            'tests' => []
        ]));

        $this->assertFalse($filter->isValid());

        $this->assertSame([
            'tests' => 'No tests are specified.'
        ], $filter->getErrors());
    }
}
