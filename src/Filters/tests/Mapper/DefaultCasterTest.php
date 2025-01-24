<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Mapper;

use PHPUnit\Framework\TestCase;
use Spiral\Filters\Exception\SetterException;
use Spiral\Filters\Model\Mapper\DefaultCaster;
use Spiral\Tests\Filters\Fixtures\AddressFilter;

final class DefaultCasterTest extends TestCase
{
    public function testSupports(): void
    {
        self::assertTrue((new DefaultCaster())->supports($this->createMock(\ReflectionNamedType::class)));
    }

    public function testSetValue(): void
    {
        $setter = new DefaultCaster();
        $filter = $this->createMock(AddressFilter::class);
        $property = new \ReflectionProperty($filter, 'city');

        $setter->setValue($filter, $property, 'foo');
        self::assertSame('foo', $property->getValue($filter));
    }

    public function testSetValueException(): void
    {
        $setter = new DefaultCaster();
        $filter = $this->createMock(AddressFilter::class);
        $property = new \ReflectionProperty($filter, 'city');

        $this->expectException(SetterException::class);
        $this->expectExceptionMessage(\sprintf(
            'Unable to set value. Cannot assign %s to property %s::$city of type string',
            \stdClass::class,
            AddressFilter::class,
        ));
        $setter->setValue($filter, $property, new \stdClass());
    }
}
