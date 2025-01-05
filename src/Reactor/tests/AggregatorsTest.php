<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use PHPUnit\Framework\TestCase;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Aggregator\Classes;
use Spiral\Reactor\Aggregator\Constants;
use Spiral\Reactor\Aggregator\Elements;
use Spiral\Reactor\Aggregator\EnumCases;
use Spiral\Reactor\Aggregator\Enums;
use Spiral\Reactor\Aggregator\Functions;
use Spiral\Reactor\Aggregator\Interfaces;
use Spiral\Reactor\Aggregator\Methods;
use Spiral\Reactor\Aggregator\Namespaces;
use Spiral\Reactor\Aggregator\Parameters;
use Spiral\Reactor\Aggregator\Properties;
use Spiral\Reactor\Aggregator\Traits;
use Spiral\Reactor\Aggregator\TraitUses;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\EnumDeclaration;
use Spiral\Reactor\Exception\ReactorException;
use Spiral\Reactor\FunctionDeclaration;
use Spiral\Reactor\InterfaceDeclaration;
use Spiral\Reactor\Partial;
use Spiral\Reactor\TraitDeclaration;

final class AggregatorsTest extends TestCase
{
    public function testClasses(): void
    {
        $aggr = new Classes([]);
        self::assertFalse($aggr->has('Test'));

        $class = new ClassDeclaration('Test');

        $aggr->add($class);
        self::assertTrue($aggr->has('Test'));
        self::assertSame($class, $aggr->get('Test'));
    }

    public function testConstants(): void
    {
        $aggr = new Constants([]);
        self::assertFalse($aggr->has('TEST'));

        $constant = new Partial\Constant('TEST');

        $aggr->add($constant);
        self::assertTrue($aggr->has('TEST'));
        self::assertSame($constant, $aggr->get('TEST'));
    }

    public function testEnumCases(): void
    {
        $aggr = new EnumCases([]);
        self::assertFalse($aggr->has('test'));

        $case = new Partial\EnumCase('test');

        $aggr->add($case);
        self::assertTrue($aggr->has('test'));
        self::assertSame($case, $aggr->get('test'));
    }

    public function testFunctions(): void
    {
        $aggr = new Functions([]);
        self::assertFalse($aggr->has('test'));

        $fn = new FunctionDeclaration('test');

        $aggr->add($fn);
        self::assertTrue($aggr->has('test'));
        self::assertSame($fn, $aggr->get('test'));
    }

    public function testMethods(): void
    {
        $aggr = new Methods([]);
        self::assertFalse($aggr->has('method'));

        $method = new Partial\Method('method');

        $aggr->add($method);
        self::assertTrue($aggr->has('method'));
        self::assertSame($method, $aggr->get('method'));
    }

    public function testNamespaces(): void
    {
        $aggr = new Namespaces([]);
        self::assertFalse($aggr->has('test'));

        $namespace = new Partial\PhpNamespace('test');

        $aggr->add($namespace);
        self::assertTrue($aggr->has('test'));
        self::assertSame($namespace, $aggr->get('test'));
    }

    public function testParameters(): void
    {
        $aggr = new Parameters([]);
        self::assertFalse($aggr->has('param'));

        $param = new Partial\Parameter('param');

        $aggr->add($param);
        self::assertTrue($aggr->has('param'));
        self::assertSame($param, $aggr->get('param'));
    }

    public function testProperties(): void
    {
        $aggr = new Properties([]);
        self::assertFalse($aggr->has('test'));

        $property = new Partial\Property('test');

        $aggr->add($property);
        self::assertTrue($aggr->has('test'));
        self::assertSame($property, $aggr->get('test'));
    }

    public function testTraitUses(): void
    {
        $aggr = new TraitUses([]);
        self::assertFalse($aggr->has('test'));

        $uses = new Partial\TraitUse('test');

        $aggr->add($uses);
        self::assertTrue($aggr->has('test'));
        self::assertSame($uses, $aggr->get('test'));
    }

    public function testElements(): void
    {
        $aggr = new Elements([]);
        self::assertFalse($aggr->has('c'));
        self::assertFalse($aggr->has('i'));
        self::assertFalse($aggr->has('t'));
        self::assertFalse($aggr->has('e'));

        $class = new ClassDeclaration('c');
        $interface = new InterfaceDeclaration('i');
        $trait = new TraitDeclaration('t');
        $enum = new EnumDeclaration('e');

        $aggr->add($class);
        $aggr->add($interface);
        $aggr->add($trait);
        $aggr->add($enum);

        self::assertTrue($aggr->has('c'));
        self::assertSame($class, $aggr->get('c'));
        self::assertTrue($aggr->has('i'));
        self::assertSame($interface, $aggr->get('i'));
        self::assertTrue($aggr->has('t'));
        self::assertSame($trait, $aggr->get('t'));
        self::assertTrue($aggr->has('e'));
        self::assertSame($enum, $aggr->get('e'));
    }

    public function testEnums(): void
    {
        $aggr = new Enums([]);
        self::assertFalse($aggr->has('test'));

        $enum = new EnumDeclaration('test');

        $aggr->add($enum);
        self::assertTrue($aggr->has('test'));
        self::assertSame($enum, $aggr->get('test'));
    }

    public function testInterfaces(): void
    {
        $aggr = new Interfaces([]);
        self::assertFalse($aggr->has('test'));

        $interface = new InterfaceDeclaration('test');

        $aggr->add($interface);
        self::assertTrue($aggr->has('test'));
        self::assertSame($interface, $aggr->get('test'));
    }

    public function testTraits(): void
    {
        $aggr = new Traits([]);
        self::assertFalse($aggr->has('test'));

        $trait = new TraitDeclaration('test');

        $aggr->add($trait);
        self::assertTrue($aggr->has('test'));
        self::assertSame($trait, $aggr->get('test'));
    }

    public function testAggregator(): void
    {
        $this->expectException(ReactorException::class);

        $a = new Aggregator([
            Partial\Method::class,
        ]);

        $a->add(new Partial\Property('method'));
    }

    public function testAggregatorNoElement(): void
    {
        $this->expectException(ReactorException::class);

        $a = new Aggregator([
            Partial\Method::class,
        ]);

        $a->get('method');
    }

    public function testAggregatorRemove(): void
    {
        $a = new Aggregator([
            Partial\Method::class,
        ]);

        $a->add(new Partial\Method('method'));
        self::assertInstanceOf(Partial\Method::class, $a->method);
        self::assertArrayHasKey('method', $a);
        self::assertInstanceOf(Partial\Method::class, $a['method']);

        self::assertTrue($a->has('method'));
        $a->remove('method');
        self::assertFalse($a->has('method'));

        $a['method'] = new Partial\Method('method');
        self::assertTrue($a->has('method'));
        unset($a['method']);
        self::assertFalse($a->has('method'));
    }
}
