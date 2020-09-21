<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder;

use PHPUnit\Framework\TestCase;

use function Spiral\Scaffolder\defineArrayType;
use function Spiral\Scaffolder\isAssociativeArray;

class FunctionsTest extends TestCase
{
    /**
     * @dataProvider associativeProvider
     * @param bool  $expected
     * @param array $array
     */
    public function testIsAssociativeArray(bool $expected, array $array): void
    {
        $this->assertEquals($expected, isAssociativeArray($array));
    }

    /**
     * @return array
     */
    public function associativeProvider(): array
    {
        return [
            [true, ['k' => 'v', 2, 3]],
            [true, [2, 3, 'k' => 'v']],
            [true, [1 => 1, 0 => 0, 2 => 2]],
            [false, [0 => 0, 1 => 1, 2 => 2]],
            [false, [0, 1, 2]],
        ];
    }

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
