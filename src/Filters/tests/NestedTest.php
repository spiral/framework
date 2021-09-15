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
use Spiral\Filters\Filter;
use Spiral\Filters\FilterInterface;
use Spiral\Tests\Filters\Fixtures\ParentFilter;
use Spiral\Tests\Filters\Fixtures\ParentOptionalFilter;

class NestedTest extends BaseTest
{
    /**
     * @param string $class
     * @param array $data
     * @return FilterInterface|object
     */
    protected function filter(string $class, array $data = []): FilterInterface
    {
        $provider = $this->getProvider();

        return $provider->createFilter($class, new ArrayInput($data));
    }

    public function testChildrenValid(): void
    {
        $filter = $this->filter(ParentFilter::class, [
            'test' => [
                'id' => 'value'
            ]
        ]);

        $this->assertTrue($filter->isValid());
        $this->assertSame('value', $filter->test->id);
    }

    public function testOptionalChildrenValid(): void
    {
        $filter = $this->filter(ParentOptionalFilter::class, [
            'test' => [
                'id' => 'value'
            ]
        ]);

        $this->assertTrue($filter->isValid());
        $this->assertSame('value', $filter->test->id);
    }

    public function testOptionalChildrenValidWithEmptyValue(): void
    {
        $filter = $this->filter(ParentOptionalFilter::class);

        $this->assertTrue($filter->isValid());
        $this->assertSame(null, $filter->test->id);
    }

    public function testChildrenInvalid(): void
    {
        $filter = $this->filter(ParentFilter::class);

        $this->assertFalse($filter->isValid());
        $this->assertSame([
            'test' => [
                'id' => 'This value is required.'
            ]
        ], $filter->getErrors());
    }

    public function testOptionalChildrenInvalid(): void
    {
        $filter = $this->filter(ParentOptionalFilter::class, [
            'test' => [
                'id' => '', // Empty value
            ]
        ]);

        $this->assertFalse($filter->isValid());
        $this->assertSame([
            'test' => [
                'id' => 'This value is required.'
            ]
        ], $filter->getErrors());
    }
}
