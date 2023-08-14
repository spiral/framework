<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Mapper;

use PHPUnit\Framework\TestCase;
use Spiral\Filters\Model\Mapper\DefaultCaster;
use Spiral\Tests\Filters\Fixtures\AddressFilter;

final class DefaultCasterTest extends TestCase
{
    public function testSupports(): void
    {
        $this->assertTrue((new DefaultCaster())->supports($this->createMock(\ReflectionNamedType::class)));
    }

    public function testSetValue(): void
    {
        $setter = new DefaultCaster();
        $filter = $this->createMock(AddressFilter::class);
        $property = new \ReflectionProperty($filter, 'city');

        $setter->setValue($filter, $property, 'foo');
        $this->assertSame('foo', $property->getValue($filter));
    }
}
