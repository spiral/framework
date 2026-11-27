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

    public function testNullValueIsFoundButNotSet(): void
    {
        $bag = new InputBag(['a' => ['b' => null]], prefix: 'a');

        self::assertTrue($bag->has('b'));
        self::assertNull($bag->get('b', 'default'));
        self::assertFalse(isset($bag['b']));
    }

    public function testMissingElementBelowPrefix(): void
    {
        $bag = new InputBag(['a' => ['b' => 'x']], prefix: 'a');

        self::assertFalse($bag->has('c'));
        self::assertFalse($bag->has('b.c'));
        self::assertSame('default', $bag->get('b.c', 'default'));
    }

    public function testPrefixPointingToScalarHasNothingBelow(): void
    {
        $bag = new InputBag(['a' => 'x'], prefix: 'a');

        self::assertFalse($bag->has('b'));
        self::assertSame('default', $bag->get('b', 'default'));
    }

    public function testMissingPrefixHasNothing(): void
    {
        $bag = new InputBag(['a' => ['b' => 'x']], prefix: 'c');

        self::assertSame([], $bag->all());
        self::assertFalse($bag->has('b'));
        self::assertCount(0, $bag);
    }

    public function testTrailingDots(): void
    {
        self::assertSame(['b' => 'x'], (new InputBag(['a' => ['b' => 'x']], prefix: 'a.'))->all());
        self::assertSame('x', (new InputBag(['a' => ['b' => 'x']], prefix: 'a'))->get('b.'));
    }

    public function testZeroKeys(): void
    {
        $bag = new InputBag(['a' => ['0' => ['0' => 'x', 'b' => 'y']]], prefix: 'a.0');

        self::assertSame('x', $bag->get('0'));
        self::assertSame('y', $bag->get('b'));
    }

    public function testRepeatedReadsOfOneBag(): void
    {
        $bag = new InputBag(['a' => ['b' => 'x', 'c' => 'y']], prefix: 'a');

        self::assertSame('x', $bag->get('b'));
        self::assertSame('y', $bag->get('c'));
        self::assertSame('x', $bag->get('b'));
        self::assertSame(['b' => 'x', 'c' => 'y'], $bag->all());
    }

    public function testWithParentBagContinuesFromWhatTheParentResolved(): void
    {
        $parent = new InputBag(['a' => ['b' => ['c' => 'parent']]], prefix: 'a');
        // A different data set proves the child reads through the parent, not from its own root.
        $child = (new InputBag(['a' => ['b' => ['c' => 'own']]], prefix: 'a.b'))->withParentBag($parent);

        self::assertSame('parent', $child->get('c'));
    }

    public function testWithParentBagReturnsNewBag(): void
    {
        $data = ['a' => ['b' => ['c' => 'x']]];
        $parent = new InputBag($data, prefix: 'a');
        $child = new InputBag($data, prefix: 'a.b');

        $linked = $child->withParentBag($parent);

        self::assertNotSame($child, $linked);
        self::assertSame(['c' => 'x'], $linked->all());
        self::assertSame(['c' => 'x'], $child->all());
    }

    public function testWithParentBagReadsAsStandaloneBag(): void
    {
        $data = ['a' => ['b' => ['c' => null, 'd' => ['e' => 'x']], 'f' => 'scalar']];
        $parent = new InputBag($data, prefix: 'a');

        foreach (['a.b', 'a.b.d', 'a.f', 'a.missing', 'a.b.c'] as $prefix) {
            $standalone = new InputBag($data, prefix: $prefix);
            $linked = (new InputBag($data, prefix: $prefix))->withParentBag($parent);

            foreach (['', 'c', 'd', 'd.e', 'e', 'missing'] as $name) {
                self::assertSame($standalone->has($name), $linked->has($name), "has('$name') under '$prefix'");
                self::assertSame(
                    $standalone->get($name, 'default'),
                    $linked->get($name, 'default'),
                    "get('$name') under '$prefix'",
                );
            }
        }
    }

    public function testWithParentBagIgnoresUnrelatedParent(): void
    {
        $data = ['a' => ['b' => 'x'], 'ab' => ['c' => 'y']];

        $child = new InputBag($data, prefix: 'ab');
        self::assertSame($child, $child->withParentBag(new InputBag($data, prefix: 'a')));

        $child = new InputBag($data, prefix: 'a');
        self::assertSame($child, $child->withParentBag(new InputBag($data, prefix: 'a')));
    }

    public function testWithParentBagIgnoresRootParent(): void
    {
        $data = ['a' => ['b' => 'x']];
        $child = new InputBag($data, prefix: 'a');

        self::assertSame($child, $child->withParentBag(new InputBag($data)));
    }
}
