<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Mapper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Filters\Exception\SetterException;
use Spiral\Filters\Model\Mapper\UuidCaster;
use Spiral\Tests\Filters\Fixtures\UserFilter;

final class UuidCasterTest extends TestCase
{
    #[DataProvider('supportsDataProvider')]
    public function testSupports(\ReflectionProperty $ref, bool $expected): void
    {
        self::assertSame($expected, (new UuidCaster())->supports($ref->getType()));
    }

    public function testSetValue(): void
    {
        $setter = new UuidCaster();
        $filter = $this->createMock(UserFilter::class);
        $property = new \ReflectionProperty($filter, 'groupUuid');

        $setter->setValue($filter, $property, '11111111-1111-1111-1111-111111111111');
        self::assertSame('11111111-1111-1111-1111-111111111111', $property->getValue($filter)->toString());
    }

    public function testSetValueException(): void
    {
        $setter = new UuidCaster();
        $filter = $this->createMock(UserFilter::class);
        $ref = new \ReflectionProperty($filter, 'friendUuid');

        $this->expectException(SetterException::class);
        $this->expectExceptionMessage('Unable to set UUID value. Invalid UUID string: foo');
        $setter->setValue($filter, $ref, 'foo');
    }

    public static function supportsDataProvider(): \Traversable
    {
        $ref = new \ReflectionClass(UserFilter::class);

        yield 'string' => [$ref->getProperty('name'), false];
        yield 'enum' => [$ref->getProperty('status'), false];
        yield 'uuid' => [$ref->getProperty('groupUuid'), true];
    }
}
