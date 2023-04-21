<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Request;

use PHPUnit\Framework\TestCase;
use Spiral\Http\Exception\InputException;
use Spiral\Http\Request\InputBag;

final class InputBagTest extends TestCase
{
    public function testEmptyBagCount(): void
    {
        $bag = new InputBag(data: []);
        $this->assertSame(0, $bag->count());

        $bag = new InputBag(data: [], prefix: 'foo');
        $this->assertSame(0, $bag->count());
    }

    public function testCount(): void
    {
        $bag = new InputBag(data: [1 => 'bar', 2 => [3 => 'foo']]);

        $this->assertSame(2, $bag->count());
        $this->assertSame(2, \count($bag));
    }

    public function testCountWithPrefix(): void
    {
        $bag = new InputBag(data: [1 => 'bar', 2 => [3 => 'foo']], prefix: '2');

        $this->assertSame(1, $bag->count());
        $this->assertSame(1, \count($bag));
    }

    public function testGetsAllData(): void
    {
        $bag = new InputBag($data = [1 => 'bar', 2 => [3 => 'foo']]);
        $this->assertSame($data, $bag->all());
    }

    public function testGetsAllDataWithPrefix(): void
    {
        $bag = new InputBag([1 => 'bar', 2 => [3 => 'foo']], prefix: '2');
        $this->assertSame([3 => 'foo'], $bag->all());
    }

    public function testIterator(): void
    {
        $bag = new InputBag($data = [1 => 'bar', 2 => [3 => 'foo']]);
        $dataFromIterator = \iterator_to_array($bag);

        $this->assertSame($data, $dataFromIterator);
    }

    public function testHas(): void
    {
        $bag = new InputBag([1 => 'bar', 2 => [3 => 'foo'], 4 => null]);

        // Key exists and not null
        $this->assertTrue($bag->has(1));
        $this->assertTrue(isset($bag[1]));
        $this->assertTrue($bag->has('1'));
        $this->assertTrue(isset($bag['1']));

        // Key exists and null
        $this->assertTrue($bag->has(4));
        $this->assertFalse(isset($bag[4]));

        // Key doesn't exist
        $this->assertFalse($bag->has(5));
        $this->assertFalse(isset($bag[5]));
    }

    public function testHasWithPrefix(): void
    {
        $bag = new InputBag([1 => 'bar', 2 => [3 => 'foo'], 4 => null], prefix: 2);

        $this->assertFalse($bag->has(1));
        $this->assertFalse(isset($bag[1]));

        $this->assertTrue($bag->has(3));
        $this->assertTrue(isset($bag[3]));
    }

    public function testGet(): void
    {
        $bag = new InputBag([1 => 'bar', 2 => [3 => 'foo'], 4 => null]);

        // Key exists and not null
        $this->assertSame('bar', $bag->get(1));
        $this->assertSame('bar', $bag->get('1'));
        $this->assertSame('bar', $bag->get(1, 'baz'));
        $this->assertSame('bar', $bag[1]);
        $this->assertSame('bar', $bag['1']);

        // Key exists and not null
        $this->assertSame('foo', $bag->get('2.3'));
        $this->assertSame('foo', $bag['2.3']);

        // Key exists and null
        $this->assertNull($bag->get(4));
        $this->assertNull($bag->get(4, 'baz'));
        $this->assertNull($bag[4]);

        // Key exists and null
        $this->assertNull($bag->get(5));
        $this->assertNull($bag->get('5.5'));
        $this->assertNull($bag['5.5']);
        $this->assertSame('baz', $bag->get('5.5', 'baz'));
    }

    public function testGetWithPrefix(): void
    {
        $bag = new InputBag([1 => 'bar', 2 => [3 => 'foo'], 4 => null], prefix: 2);

        // Key exists and not null
        $this->assertSame('foo', $bag->get(3));

        // Key doesn't exist
        $this->assertNull($bag->get(4));
    }

    public function testSet(): void
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('InputBag is immutable');
        $bag = new InputBag([1 => 'bar']);
        $bag[1] = 'foo';
    }

    public function testUnset(): void
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('InputBag is immutable');
        $bag = new InputBag([1 => 'bar']);
        unset($bag[1]);
    }

    public function testFetch(): void
    {
        $bag = new InputBag([1 => 'bar', 2 => [3 => 'foo'], 4 => null]);

        $result = $bag->fetch(['1', 4, 5]);
        $this->assertSame([1 => 'bar', 4 => null], $result);

        // Fill missed values with null
        $result = $bag->fetch(['1', 4, 5], true);
        $this->assertSame([1 => 'bar', 4 => null, 5 => null], $result);

        // Fill missed values with filler
        $result = $bag->fetch(['1', 4, 5], true, 'baz');
        $this->assertSame([1 => 'bar', 4 => null, 5 => 'baz'], $result);
    }

    public function testFetchWithPrefix(): void
    {
        $bag = new InputBag([1 => 'bar', 2 => [3 => 'foo'], 4 => null], prefix: 2);

        $result = $bag->fetch(['1', 4, 3]);
        $this->assertSame([3 => 'foo'], $result);

        // Fill missed values with null
        $result = $bag->fetch(['1', 4, 3], true);
        $this->assertSame([3 => 'foo', 1 => null, 4 => null], $result);

        // Fill missed values with filler
        $result = $bag->fetch(['1', 4, 3], true, 'baz');
        $this->assertSame([3 => 'foo', 1 => 'baz', 4 => 'baz'], $result);
    }
}
