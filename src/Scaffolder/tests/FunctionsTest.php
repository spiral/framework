<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Spiral\Scaffolder\defineArrayType;

class FunctionsTest extends TestCase
{
    #[DataProvider('defineProvider')]
    public function testDefineArrayType(?string $expected, array $array, ?string $failureType): void
    {
        $this->assertEquals($expected, defineArrayType($array, $failureType));
    }

    public static function defineProvider(): \Traversable
    {
        //valid
        yield ['integer', [1, 2, 3], null];
        yield ['NULL', [null, null], null];

        //mixed
        yield [null, [1, '2', 3], null];
        yield [null, [null, 'null'], null];
        yield ['mixed', [null, 'null'], 'mixed'];
    }
}
