<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Mapper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Filters\Exception\SetterException;
use Spiral\Filters\Model\Mapper\EnumCaster;
use Spiral\Tests\Filters\Fixtures\Status;
use Spiral\Tests\Filters\Fixtures\UserFilter;

final class EnumCasterTest extends TestCase
{
    #[DataProvider('supportsDataProvider')]
    public function testSupports(\ReflectionProperty $ref, bool $expected): void
    {
        $this->assertSame($expected, (new EnumCaster())->supports($ref->getType()));
    }

    public function testSetValue(): void
    {
        $setter = new EnumCaster();
        $filter = $this->createMock(UserFilter::class);
        $property = new \ReflectionProperty($filter, 'status');

        $setter->setValue($filter, $property, 'active');
        $this->assertEquals(Status::Active, $property->getValue($filter));
    }

    public function testSetValueException(): void
    {
        $setter = new EnumCaster();
        $filter = $this->createMock(UserFilter::class);
        $property = new \ReflectionProperty($filter, 'status');

        $this->expectException(SetterException::class);
        $setter->setValue($filter, $property, 'foo');
    }

    public static function supportsDataProvider(): \Traversable
    {
        $ref = new \ReflectionClass(UserFilter::class);

        yield 'enum' => [$ref->getProperty('status'), true];
        yield 'uuid' => [$ref->getProperty('groupUuid'), false];
    }
}
