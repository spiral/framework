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
use Spiral\Tests\Filters\Fixtures\ArrayIterateByFilter;

class ArrayIterateByTest extends BaseTest
{
    public function testValid(): void
    {
        $filter = $this->getProvider()->createFilter(ArrayIterateByFilter::class, new ArrayInput([
            'tests' => [
                ['id' => 'value'],
                ['id' => 'value2'],
            ],
            'by'    => [1, 2]
        ]));

        $this->assertTrue($filter->isValid());

        $this->assertSame('value', $filter->tests[0]->id);
        $this->assertSame('value2', $filter->tests[1]->id);
    }

    public function testInvalid(): void
    {
        $filter = $this->getProvider()->createFilter(ArrayIterateByFilter::class, new ArrayInput([
            'tests' => [
                'a' => ['id' => 'value'],
                'b' => ['id' => null],
            ],
            'by'    => ['a' => 1, 'b' => 2, 'c' => 3]
        ]));

        $this->assertFalse($filter->isValid());

        $this->assertSame('value', $filter->tests['a']->id);
        $this->assertNull($filter->tests['b']->id);
        $this->assertNull($filter->tests['c']->id);

        $this->assertSame([
            'tests' => [
                'b' => [
                    'id' => 'This value is required.'
                ],
                'c' => [
                    'id' => 'This value is required.'
                ],
            ]
        ], $filter->getErrors());
    }

    public function testEmptyValid(): void
    {
        $filter = $this->getProvider()->createFilter(ArrayIterateByFilter::class, new ArrayInput([]));
        $this->assertTrue($filter->isValid());
    }
}
