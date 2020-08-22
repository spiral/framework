<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Filters;

use PHPUnit\Framework\TestCase;
use Spiral\Filters\ArrayInput;

class ArrayInputTest extends TestCase
{
    public function testGetValue(): void
    {
        $arr = new ArrayInput(['key' => 'value']);
        $this->assertSame('value', $arr->getValue('', 'key'));
        $this->assertNull($arr->getValue('', 'other'));
    }

    public function testGetValueNested(): void
    {
        $arr = new ArrayInput(['key' => ['a' => 'b']]);
        $this->assertSame('b', $arr->getValue('', 'key.a'));
        $this->assertNull($arr->getValue('', 'key.c'));
        $this->assertNull($arr->getValue('', 'key.a.d'));
    }

    public function testSliced(): void
    {
        $arr = new ArrayInput(['key' => ['a' => 'b']]);
        $this->assertSame('b', $arr->getValue('', 'key.a'));

        $arr2 = $arr->withPrefix('key');
        $this->assertSame('b', $arr->getValue('', 'key.a'));
        $this->assertSame('b', $arr2->getValue('', 'a'));
    }

    public function testSlicedOverwrite(): void
    {
        $arr = new ArrayInput(['key' => ['a' => ['x' => 'y']]]);
        $this->assertSame('y', $arr->getValue('', 'key.a.x'));

        $arr2 = $arr->withPrefix('key');
        $this->assertSame('y', $arr2->getValue('', 'a.x'));

        $arr3 = $arr2->withPrefix('a');
        $this->assertSame('y', $arr3->getValue('', 'x'));

        // return
        $arr4 = $arr3->withPrefix('key', false);
        $this->assertSame('y', $arr4->getValue('', 'a.x'));
    }

    public function testGetSlice(): void
    {
        $arr = new ArrayInput(['key' => ['a' => ['x' => 'y']]]);
        $this->assertSame(['key' => ['a' => ['x' => 'y']]], $arr->getValue('', ''));
    }
}
