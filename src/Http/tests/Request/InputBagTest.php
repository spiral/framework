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
        self::assertCount(0, $bag);

        $bag = new InputBag(data: [], prefix: 'foo');
        self::assertCount(0, $bag);
    }

    public function testCount(): void
    {
        $bag = new InputBag(data: [1 => 'bar', 2 => [3 => 'foo']]);

        self::assertCount(2, $bag);
        self::assertCount(2, $bag);
    }

    public function testCountWithPrefix(): void
    {
        $bag = new InputBag(data: [1 => 'bar', 2 => [3 => 'foo']], prefix: '2');

        self::assertCount(1, $bag);
        self::assertCount(1, $bag);
    }

    public function testGetsAllData(): void
    {
        $bag = new InputBag($data = [1 => 'bar', 2 => [3 => 'foo']]);
        self::assertSame($data, $bag->all());
    }

    public function testGetsAllDataWithPrefix(): void
    {
        $bag = new InputBag([1 => 'bar', 2 => [3 => 'foo']], prefix: '2');
        self::assertSame([3 => 'foo'], $bag->all());
    }

    public function testIterator(): void
    {
        $bag = new InputBag($data = [1 => 'bar', 2 => [3 => 'foo']]);
        $dataFromIterator = \iterator_to_array($bag);

        self::assertSame($data, $dataFromIterator);
    }

    public function testHas(): void
    {
        $bag = new InputBag([1 => 'bar', 2 => [3 => 'foo'], 4 => null]);

        // Key exists and not null
        self::assertTrue($bag->has(1));
        self::assertArrayHasKey(1, $bag);
        self::assertTrue($bag->has('1'));
        self::assertArrayHasKey('1', $bag);

        // Key exists and null
        self::assertTrue($bag->has(4));
        self::assertArrayNotHasKey(4, $bag);

        // Key doesn't exist
        self::assertFalse($bag->has(5));
        self::assertArrayNotHasKey(5, $bag);
    }

    public function testHasWithPrefix(): void
    {
        $bag = new InputBag([1 => 'bar', 2 => [3 => 'foo'], 4 => null], prefix: 2);

        self::assertFalse($bag->has(1));
        self::assertArrayNotHasKey(1, $bag);

        self::assertTrue($bag->has(3));
        self::assertArrayHasKey(3, $bag);
    }

    public function testGet(): void
    {
        $bag = new InputBag([1 => 'bar', 2 => [3 => 'foo'], 4 => null]);

        // Key exists and not null
        self::assertSame('bar', $bag->get(1));
        self::assertSame('bar', $bag->get('1'));
        self::assertSame('bar', $bag->get(1, 'baz'));
        self::assertSame('bar', $bag[1]);
        self::assertSame('bar', $bag['1']);

        // Key exists and not null
        self::assertSame('foo', $bag->get('2.3'));
        self::assertSame('foo', $bag['2.3']);

        // Key exists and null
        self::assertNull($bag->get(4));
        self::assertNull($bag->get(4, 'baz'));
        self::assertNull($bag[4]);

        // Key exists and null
        self::assertNull($bag->get(5));
        self::assertNull($bag->get('5.5'));
        self::assertNull($bag['5.5']);
        self::assertSame('baz', $bag->get('5.5', 'baz'));
    }

    public function testGetWithPrefix(): void
    {
        $bag = new InputBag([1 => 'bar', 2 => [3 => 'foo'], 4 => null], prefix: 2);

        // Key exists and not null
        self::assertSame('foo', $bag->get(3));

        // Key doesn't exist
        self::assertNull($bag->get(4));
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
        self::assertSame([1 => 'bar', 4 => null], $result);

        // Fill missed values with null
        $result = $bag->fetch(['1', 4, 5], true);
        self::assertSame([1 => 'bar', 4 => null, 5 => null], $result);

        // Fill missed values with filler
        $result = $bag->fetch(['1', 4, 5], true, 'baz');
        self::assertSame([1 => 'bar', 4 => null, 5 => 'baz'], $result);
    }

    public function testFetchWithPrefix(): void
    {
        $bag = new InputBag([1 => 'bar', 2 => [3 => 'foo'], 4 => null], prefix: 2);

        $result = $bag->fetch(['1', 4, 3]);
        self::assertSame([3 => 'foo'], $result);

        // Fill missed values with null
        $result = $bag->fetch(['1', 4, 3], true);
        self::assertSame([3 => 'foo', 1 => null, 4 => null], $result);

        // Fill missed values with filler
        $result = $bag->fetch(['1', 4, 3], true, 'baz');
        self::assertSame([3 => 'foo', 1 => 'baz', 4 => 'baz'], $result);
    }
}
