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
use Spiral\Tests\Filters\Fixtures\ArrayIterateByPathFilter;

class ArrayIterateByPathFilterTest extends BaseTest
{
    public function testValid(): void
    {
        $filter = $this->getProvider()->createFilter(ArrayIterateByPathFilter::class, new ArrayInput([
            'custom' => [
                0 => ['id' => 'value'],
                1 => ['id' => 'value2'],
            ],
            'by'     => [0 => 'value', 1 => 'value']
        ]));

        $this->assertTrue($filter->isValid());

        $this->assertSame('value', $filter->tests[0]->id);
        $this->assertSame('value2', $filter->tests[1]->id);
    }

    public function testExcludeElement(): void
    {
        $filter = $this->getProvider()->createFilter(ArrayIterateByPathFilter::class, new ArrayInput([
            'custom' => [
                0 => ['id' => 'value'],
                1 => ['id' => 'value2'],
            ],
            'by'     => [0 => 'value']
        ]));

        $this->assertTrue($filter->isValid());

        $this->assertSame('value', $filter->tests[0]->id);
        $this->assertFalse(isset($filter->tests[1]));
    }

    public function testInvalid(): void
    {
        $filter = $this->getProvider()->createFilter(ArrayIterateByPathFilter::class, new ArrayInput([
            'custom' => [
                'a' => ['id' => 'value'],
                'b' => ['id' => null],
            ],
            'by'     => [
                'a' => 1,
                'b' => 2,
                'c' => 3
            ]
        ]));

        $this->assertFalse($filter->isValid());

        $this->assertSame('value', $filter->tests['a']->id);
        $this->assertNull($filter->tests['b']->id);
        $this->assertNull($filter->tests['c']->id);

        $this->assertSame([
            'custom' => [
                'b' => ['id' => 'This value is required.'],
                'c' => ['id' => 'This value is required.'],
            ]
        ], $filter->getErrors());
    }

    public function testEmptyValid(): void
    {
        $filter = $this->getProvider()->createFilter(ArrayIterateByPathFilter::class, new ArrayInput([]));
        $this->assertTrue($filter->isValid());
    }
}
