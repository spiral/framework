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
        $this->assertFalse($aggr->has('Test'));

        $class = new ClassDeclaration('Test');

        $aggr->add($class);
        $this->assertTrue($aggr->has('Test'));
        $this->assertSame($class, $aggr->get('Test'));
    }

    public function testConstants(): void
    {
        $aggr = new Constants([]);
        $this->assertFalse($aggr->has('TEST'));

        $constant = new Partial\Constant('TEST');

        $aggr->add($constant);
        $this->assertTrue($aggr->has('TEST'));
        $this->assertSame($constant, $aggr->get('TEST'));
    }

    public function testEnumCases(): void
    {
        $aggr = new EnumCases([]);
        $this->assertFalse($aggr->has('test'));

        $case = new Partial\EnumCase('test');

        $aggr->add($case);
        $this->assertTrue($aggr->has('test'));
        $this->assertSame($case, $aggr->get('test'));
    }

    public function testFunctions(): void
    {
        $aggr = new Functions([]);
        $this->assertFalse($aggr->has('test'));

        $fn = new FunctionDeclaration('test');

        $aggr->add($fn);
        $this->assertTrue($aggr->has('test'));
        $this->assertSame($fn, $aggr->get('test'));
    }

    public function testMethods(): void
    {
        $aggr = new Methods([]);
        $this->assertFalse($aggr->has('method'));

        $method = new Partial\Method('method');

        $aggr->add($method);
        $this->assertTrue($aggr->has('method'));
        $this->assertSame($method, $aggr->get('method'));
    }

    public function testNamespaces(): void
    {
        $aggr = new Namespaces([]);
        $this->assertFalse($aggr->has('test'));

        $namespace = new Partial\PhpNamespace('test');

        $aggr->add($namespace);
        $this->assertTrue($aggr->has('test'));
        $this->assertSame($namespace, $aggr->get('test'));
    }

    public function testParameters(): void
    {
        $aggr = new Parameters([]);
        $this->assertFalse($aggr->has('param'));

        $param = new Partial\Parameter('param');

        $aggr->add($param);
        $this->assertTrue($aggr->has('param'));
        $this->assertSame($param, $aggr->get('param'));
    }

    public function testProperties(): void
    {
        $aggr = new Properties([]);
        $this->assertFalse($aggr->has('test'));

        $property = new Partial\Property('test');

        $aggr->add($property);
        $this->assertTrue($aggr->has('test'));
        $this->assertSame($property, $aggr->get('test'));
    }

    public function testTraitUses(): void
    {
        $aggr = new TraitUses([]);
        $this->assertFalse($aggr->has('test'));

        $uses = new Partial\TraitUse('test');

        $aggr->add($uses);
        $this->assertTrue($aggr->has('test'));
        $this->assertSame($uses, $aggr->get('test'));
    }

    public function testElements(): void
    {
        $aggr = new Elements([]);
        $this->assertFalse($aggr->has('c'));
        $this->assertFalse($aggr->has('i'));
        $this->assertFalse($aggr->has('t'));
        $this->assertFalse($aggr->has('e'));

        $class = new ClassDeclaration('c');
        $interface = new InterfaceDeclaration('i');
        $trait = new TraitDeclaration('t');
        $enum = new EnumDeclaration('e');

        $aggr->add($class);
        $aggr->add($interface);
        $aggr->add($trait);
        $aggr->add($enum);

        $this->assertTrue($aggr->has('c'));
        $this->assertSame($class, $aggr->get('c'));
        $this->assertTrue($aggr->has('i'));
        $this->assertSame($interface, $aggr->get('i'));
        $this->assertTrue($aggr->has('t'));
        $this->assertSame($trait, $aggr->get('t'));
        $this->assertTrue($aggr->has('e'));
        $this->assertSame($enum, $aggr->get('e'));
    }

    public function testEnums(): void
    {
        $aggr = new Enums([]);
        $this->assertFalse($aggr->has('test'));

        $enum = new EnumDeclaration('test');

        $aggr->add($enum);
        $this->assertTrue($aggr->has('test'));
        $this->assertSame($enum, $aggr->get('test'));
    }

    public function testInterfaces(): void
    {
        $aggr = new Interfaces([]);
        $this->assertFalse($aggr->has('test'));

        $interface = new InterfaceDeclaration('test');

        $aggr->add($interface);
        $this->assertTrue($aggr->has('test'));
        $this->assertSame($interface, $aggr->get('test'));
    }

    public function testTraits(): void
    {
        $aggr = new Traits([]);
        $this->assertFalse($aggr->has('test'));

        $trait = new TraitDeclaration('test');

        $aggr->add($trait);
        $this->assertTrue($aggr->has('test'));
        $this->assertSame($trait, $aggr->get('test'));
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
        $this->assertInstanceOf(Partial\Method::class, $a->method);
        $this->assertTrue(isset($a['method']));
        $this->assertInstanceOf(Partial\Method::class, $a['method']);

        $this->assertTrue($a->has('method'));
        $a->remove('method');
        $this->assertFalse($a->has('method'));

        $a['method'] = new Partial\Method('method');
        $this->assertTrue($a->has('method'));
        unset($a['method']);
        $this->assertFalse($a->has('method'));
    }
}
