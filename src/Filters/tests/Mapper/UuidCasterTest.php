<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Mapper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Filters\Model\Mapper\UuidCaster;
use Spiral\Tests\Filters\Fixtures\UserFilter;

final class UuidCasterTest extends TestCase
{
    #[DataProvider('supportsDataProvider')]
    public function testSupports(\ReflectionProperty $ref, bool $expected): void
    {
        $this->assertSame($expected, (new UuidCaster())->supports($ref->getType()));
    }

    public function testSetValue(): void
    {
        $setter = new UuidCaster();
        $filter = $this->createMock(UserFilter::class);
        $property = new \ReflectionProperty($filter, 'groupUuid');

        $setter->setValue($filter, $property, '11111111-1111-1111-1111-111111111111');
        $this->assertSame('11111111-1111-1111-1111-111111111111', $property->getValue($filter)->toString());
    }

    public static function supportsDataProvider(): \Traversable
    {
        $ref = new \ReflectionClass(UserFilter::class);

        yield 'enum' => [$ref->getProperty('status'), false];
        yield 'uuid' => [$ref->getProperty('groupUuid'), true];
    }
}
