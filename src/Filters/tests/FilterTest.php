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
use Spiral\Tests\Filters\Fixtures\TestFilter;

class FilterTest extends BaseTest
{
    public function testValid(): void
    {
        $filter = $this->getProvider()->createFilter(TestFilter::class, new ArrayInput([
            'id' => 'value'
        ]));

        $this->assertTrue($filter->isValid());
        $this->assertSame('value', $filter->id);
    }

    public function testInvalid(): void
    {
        $filter = $this->getProvider()->createFilter(TestFilter::class, new ArrayInput([
            'key' => 'value'
        ]));

        $this->assertFalse($filter->isValid());

        $this->assertSame([
            'id' => 'This value is required.'
        ], $filter->getErrors());
    }

    public function testContext(): void
    {
        $filter = $this->getProvider()->createFilter(TestFilter::class, new ArrayInput([
            'id' => 'value'
        ]));

        $this->assertNull($filter->getContext());
        $this->assertTrue($filter->isValid());

        $filter->setContext('value');
        $this->assertSame('value', $filter->getContext());
        $this->assertTrue($filter->isValid());

        $filter->setContext(null);
        $this->assertNull($filter->getContext());
        $this->assertTrue($filter->isValid());
    }

    public function testSetRevalidate(): void
    {
        $filter = $this->getProvider()->createFilter(TestFilter::class, new ArrayInput([
            'id' => 'value'
        ]));

        $this->assertTrue($filter->isValid());
        $filter->id = null;
        $this->assertFalse($filter->isValid());
    }

    public function testUnsetRevalidate(): void
    {
        $filter = $this->getProvider()->createFilter(TestFilter::class, new ArrayInput([
            'id' => 'value'
        ]));

        $this->assertTrue($filter->isValid());
        unset($filter->id);
        $this->assertFalse($filter->isValid());
    }

    public function testDebugInfo(): void
    {
        $filter = $this->getProvider()->createFilter(TestFilter::class, new ArrayInput([
            'id' => 'value'
        ]));

        $info = $filter->__debugInfo();

        $this->assertSame([
            'valid'  => true,
            'fields' => [
                'id' => 'value',
            ],
            'errors' => []
        ], $info);
    }
}
