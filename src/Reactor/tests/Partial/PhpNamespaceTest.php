<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor\Partial;

use PHPUnit\Framework\TestCase;
use Nette\PhpGenerator\PhpNamespace as NettePhpNamespace;
use Spiral\Reactor\Aggregator\Classes;
use Spiral\Reactor\Aggregator\Elements;
use Spiral\Reactor\Aggregator\Enums;
use Spiral\Reactor\Aggregator\Interfaces;
use Spiral\Reactor\Aggregator\Traits;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\EnumDeclaration;
use Spiral\Reactor\FileDeclaration;
use Spiral\Reactor\InterfaceDeclaration;
use Spiral\Reactor\Partial\PhpNamespace;
use Spiral\Reactor\TraitDeclaration;

final class PhpNamespaceTest extends TestCase
{
    public function testGetName(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $this->assertSame('Foo\\Bar', $namespace->getName());
    }

    public function testBracketedSyntax(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $this->assertFalse($namespace->hasBracketedSyntax());

        $file = new FileDeclaration();
        $file->addNamespace('Bar\\Baz');
        $file->addNamespace('');
        $file->addNamespace($namespace);

        $this->assertTrue($namespace->hasBracketedSyntax());
    }

    public function testUse(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $this->assertEmpty($namespace->getUses());

        $namespace->addUse('foo');
        $namespace->addUse('bar', 'baz');
        $this->assertSame(['baz' => 'bar', 'foo' => 'foo'], $namespace->getUses());
        $this->assertStringContainsString('use bar as baz;', $namespace->__toString());
        $this->assertStringContainsString('use foo;', $namespace->__toString());

        $namespace->removeUse('bar');
        $this->assertSame(['foo' => 'foo'], $namespace->getUses());
    }

    public function testUseFunction(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $this->assertEmpty($namespace->getUses(NettePhpNamespace::NameFunction));

        $namespace->addUseFunction('foo');
        $namespace->addUseFunction('bar', 'baz');
        $this->assertSame(['baz' => 'bar', 'foo' => 'foo'], $namespace->getUses(NettePhpNamespace::NameFunction));
        $this->assertStringContainsString('use function bar as baz;', $namespace->__toString());
        $this->assertStringContainsString('use function foo;', $namespace->__toString());

        $namespace->removeUse('bar', NettePhpNamespace::NameFunction);
        $this->assertSame(['foo' => 'foo'], $namespace->getUses(NettePhpNamespace::NameFunction));
    }

    public function testUseConstant(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $this->assertEmpty($namespace->getUses(NettePhpNamespace::NameConstant));

        $namespace->addUseConstant('foo');
        $namespace->addUseConstant('bar', 'baz');
        $this->assertSame(['baz' => 'bar', 'foo' => 'foo'], $namespace->getUses(NettePhpNamespace::NameConstant));
        $this->assertStringContainsString('use const bar as baz;', $namespace->__toString());
        $this->assertStringContainsString('use const foo;', $namespace->__toString());

        $namespace->removeUse('bar', NettePhpNamespace::NameConstant);
        $this->assertSame(['foo' => 'foo'], $namespace->getUses(NettePhpNamespace::NameConstant));
    }

    public function testClass(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $class = $namespace->addClass('Test');

        $this->assertInstanceOf(ClassDeclaration::class, $class);
        $this->assertSame('Test', $class->getName());
        $this->assertCount(1, $namespace->getClasses());
        $this->assertTrue($namespace->getClasses()->has('Test'));

        $namespace->removeElement('Test');
        $this->assertCount(0, $namespace->getClasses());
        $this->assertFalse($namespace->getClasses()->has('Test'));
    }

    public function testAddInterface(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $interface = $namespace->addInterface('Test');

        $this->assertInstanceOf(InterfaceDeclaration::class, $interface);
        $this->assertSame('Test', $interface->getName());
    }

    public function testAddTrait(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $trait = $namespace->addTrait('Test');

        $this->assertInstanceOf(TraitDeclaration::class, $trait);
        $this->assertSame('Test', $trait->getName());
    }

    public function testAddEnum(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $enum = $namespace->addEnum('Test');

        $this->assertInstanceOf(EnumDeclaration::class, $enum);
        $this->assertSame('Test', $enum->getName());
    }

    public function testRender(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $this->assertStringContainsString('namespace Foo\\Bar;', $namespace->__toString());
    }

    public function testFromElement(): void
    {
        $namespace = PhpNamespace::fromElement(new NettePhpNamespace('Foo\\Bar'));

        $this->assertInstanceOf(PhpNamespace::class, $namespace);
        $this->assertSame('Foo\\Bar', $namespace->getName());
    }

    public function testGetElement(): void
    {
        $element = (new PhpNamespace('Foo\\Bar'))->getElement();

        $this->assertInstanceOf(NettePhpNamespace::class, $element);
        $this->assertSame('Foo\\Bar', $element->getName());
    }

    /**
     * @dataProvider classesDataProvider
     */
    public function testGetClasses(PhpNamespace $namespace, Classes $expected): void
    {
        $this->assertEquals($namespace->getClasses(), $expected);
    }

    /**
     * @dataProvider interfacesDataProvider
     */
    public function testGetInterfaces(PhpNamespace $namespace, Interfaces $expected): void
    {
        $this->assertEquals($namespace->getInterfaces(), $expected);
    }

    /**
     * @dataProvider traitsDataProvider
     */
    public function testGetTraits(PhpNamespace $namespace, Traits $expected): void
    {
        $this->assertEquals($namespace->getTraits(), $expected);
    }

    /**
     * @dataProvider enumsDataProvider
     */
    public function testGetEnums(PhpNamespace $namespace, Enums $expected): void
    {
        $this->assertEquals($namespace->getEnums(), $expected);
    }

    /**
     * @dataProvider elementsDataProvider
     */
    public function testGetElements(PhpNamespace $namespace, Elements $expected): void
    {
        $this->assertEquals($namespace->getElements(), $expected);
    }

    public function classesDataProvider(): \Traversable
    {
        $withoutClasses = new PhpNamespace('a');
        $withoutClasses->addInterface('b');
        $withoutClasses->addTrait('c');
        $withoutClasses->addEnum('d');

        $onlyOneClass = new PhpNamespace('b');
        $a = $onlyOneClass->addClass('a');

        $onlyClasses = new PhpNamespace('c');
        $b = $onlyClasses->addClass('b');
        $c = $onlyClasses->addClass('c');

        $withOtherElements = new PhpNamespace('d');
        $d = $withOtherElements->addClass('d');
        $withOtherElements->addInterface('b');
        $withOtherElements->addTrait('c');
        $withOtherElements->addEnum('l');

        yield [new PhpNamespace('a'), new Classes([])];
        yield [$withoutClasses, new Classes([])];
        yield [$onlyOneClass, new Classes(['a' => $a])];
        yield [$onlyClasses, new Classes(['b' => $b, 'c' => $c])];
        yield [$withOtherElements, new Classes(['d' => $d])];
    }

    public function interfacesDataProvider(): \Traversable
    {
        $withoutInterfaces = new PhpNamespace('a');
        $withoutInterfaces->addClass('b');
        $withoutInterfaces->addTrait('c');
        $withoutInterfaces->addEnum('d');

        $onlyOneInterface = new PhpNamespace('b');
        $a = $onlyOneInterface->addInterface('a');

        $onlyInterfaces = new PhpNamespace('c');
        $b = $onlyInterfaces->addInterface('b');
        $c = $onlyInterfaces->addInterface('c');

        $withOtherElements = new PhpNamespace('d');
        $withOtherElements->addClass('j');
        $d = $withOtherElements->addInterface('d');
        $withOtherElements->addTrait('l');
        $withOtherElements->addEnum('k');

        yield [new PhpNamespace('a'), new Interfaces([])];
        yield [$withoutInterfaces, new Interfaces([])];
        yield [$onlyOneInterface, new Interfaces(['a' => $a])];
        yield [$onlyInterfaces, new Interfaces(['b' => $b, 'c' => $c])];
        yield [$withOtherElements, new Interfaces(['d' => $d])];
    }

    public function traitsDataProvider(): \Traversable
    {
        $withoutTraits = new PhpNamespace('a');
        $withoutTraits->addClass('b');
        $withoutTraits->addInterface('c');
        $withoutTraits->addEnum('d');

        $onlyOneTrait = new PhpNamespace('b');
        $a = $onlyOneTrait->addTrait('a');

        $onlyTraits = new PhpNamespace('c');
        $b = $onlyTraits->addTrait('b');
        $c = $onlyTraits->addTrait('c');

        $withOtherElements = new PhpNamespace('d');
        $withOtherElements->addClass('a');
        $withOtherElements->addInterface('f');
        $d = $withOtherElements->addTrait('d');
        $withOtherElements->addEnum('l');

        yield [new PhpNamespace('a'), new Traits([])];
        yield [$withoutTraits, new Traits([])];
        yield [$onlyOneTrait, new Traits(['a' => $a])];
        yield [$onlyTraits, new Traits(['b' => $b, 'c' => $c])];
        yield [$withOtherElements, new Traits(['d' => $d])];
    }

    public function enumsDataProvider(): \Traversable
    {
        $withoutEnums = new PhpNamespace('a');
        $withoutEnums->addClass('b');
        $withoutEnums->addInterface('c');
        $withoutEnums->addTrait('d');

        $onlyOneEnum = new PhpNamespace('b');
        $a = $onlyOneEnum->addEnum('a');

        $onlyEnums = new PhpNamespace('c');
        $b = $onlyEnums->addEnum('b');
        $c = $onlyEnums->addEnum('c');

        $withOtherElements = new PhpNamespace('d');
        $withOtherElements->addClass('a');
        $withOtherElements->addInterface('b');
        $withOtherElements->addTrait('c');
        $d = $withOtherElements->addEnum('d');

        yield [new PhpNamespace('a'), new Enums([])];
        yield [$withoutEnums, new Enums([])];
        yield [$onlyOneEnum, new Enums(['a' => $a])];
        yield [$onlyEnums, new Enums(['b' => $b, 'c' => $c])];
        yield [$withOtherElements, new Enums(['d' => $d])];
    }

    public function elementsDataProvider(): \Traversable
    {
        $class = new PhpNamespace('a');
        $a = $class->addClass('a');

        $interface = new PhpNamespace('a');
        $b = $interface->addInterface('b');

        $trait = new PhpNamespace('a');
        $c = $trait->addTrait('c');

        $enum = new PhpNamespace('a');
        $d = $enum->addEnum('d');

        $all = new PhpNamespace('a');
        $e = $all->addEnum('e');
        $f = $all->addClass('f');
        $g = $all->addInterface('g');
        $h = $all->addTrait('h');

        yield [new PhpNamespace('a'), new Elements([])];
        yield [$class, new Elements(['a' => $a])];
        yield [$interface, new Elements(['b' => $b])];
        yield [$trait, new Elements(['c' => $c])];
        yield [$enum, new Elements(['d' => $d])];
        yield [$all, new Elements([
            'e' => $e,
            'f' => $f,
            'g' => $g,
            'h' => $h
        ])];
    }
}
