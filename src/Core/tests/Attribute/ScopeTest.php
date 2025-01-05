<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Attribute\Scope;
use Spiral\Framework\Spiral;
use Spiral\Tests\Core\Fixtures\ScopeEnum;

final class ScopeTest extends TestCase
{
    #[DataProvider('scopeNameDataProvider')]
    public function testScope(string|\BackedEnum $name, string $expected): void
    {
        $scope = new Scope($name);

        self::assertSame($expected, $scope->name);
    }

    public static function scopeNameDataProvider(): \Traversable
    {
        yield ['foo', 'foo'];
        yield [Spiral::HttpRequest, 'http-request'];
        yield [ScopeEnum::A, 'a'];
    }
}
