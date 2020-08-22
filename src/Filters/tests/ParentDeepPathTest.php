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
use Spiral\Tests\Filters\Fixtures\ParentDeepPathFilter;

class ParentDeepPathTest extends BaseTest
{
    public function testChildrenValid(): void
    {
        $filter = $this->getProvider()->createFilter(ParentDeepPathFilter::class, new ArrayInput([
            'custom' => [
                'test' => [
                    'id' => 'value'
                ]
            ]
        ]));

        $this->assertTrue($filter->isValid());
        $this->assertSame('value', $filter->test->id);
    }

    public function testChildrenInvalid(): void
    {
        $filter = $this->getProvider()->createFilter(ParentDeepPathFilter::class, new ArrayInput([]));

        $this->assertFalse($filter->isValid());
        $this->assertSame([
            'custom' => [
                'test' => [
                    'id' => 'This value is required.'
                ]
            ]
        ], $filter->getErrors());
    }
}
