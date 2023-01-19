<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder;

use PHPUnit\Framework\TestCase;

use function Spiral\Scaffolder\defineArrayType;

class FunctionsTest extends TestCase
{
    /**
     * @dataProvider defineProvider
     * @param mixed       $expected
     * @param array       $array
     * @param string|null $failureType
     */
    public function testDefineArrayType($expected, array $array, ?string $failureType): void
    {
        $this->assertEquals($expected, defineArrayType($array, $failureType));
    }

    /**
     * @return array
     */
    public function defineProvider(): array
    {
        return [
            //valid
            ['integer', [1, 2, 3], null],
            ['NULL', [null, null], null],

            //mixed
            [null, [1, '2', 3], null],
            [null, [null, 'null'], null],
            ['mixed', [null, 'null'], 'mixed'],
        ];
    }
}
