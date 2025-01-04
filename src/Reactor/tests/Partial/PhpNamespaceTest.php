<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor\Partial;

use Nette\PhpGenerator\PhpNamespace as NettePhpNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Reactor\Aggregator\Classes;
use Spiral\Reactor\Aggregator\Elements;
use Spiral\Reactor\Aggregator\Enums;
use Spiral\Reactor\Aggregator\Interfaces;
use Spiral\Reactor\Aggregator\Traits;
use Spiral\Reactor\FileDeclaration;
use Spiral\Reactor\Partial\PhpNamespace;
use Spiral\Tests\Reactor\BaseWithElementsTestCase;

final class PhpNamespaceTest extends BaseWithElementsTestCase
{
    public function testGetName(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        self::assertSame('Foo\\Bar', $namespace->getName());
    }

    public function testBracketedSyntax(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        self::assertFalse($namespace->hasBracketedSyntax());

        $file = new FileDeclaration();
        $file->addNamespace('Bar\\Baz');
        $file->addNamespace('');
        $file->addNamespace($namespace);

        self::assertTrue($namespace->hasBracketedSyntax());
    }

    public function testAddUse(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        self::assertEmpty($namespace->getUses());

        $namespace->addUse('Some\\Other');
        $namespace->addUse('Other\\Some', 'Baz');
        $namespace->addClass('Test')->setExtends('Some\\Other')->addImplement('Baz');

        self::assertSame(['Baz' => 'Other\\Some', 'Other' => 'Some\\Other'], $namespace->getUses());

        self::assertStringContainsString('use Other\\Some as Baz;', $namespace->__toString());
        self::assertStringContainsString('use Some\\Other;', $namespace->__toString());

        $namespace->removeUse('Other\\Some');
        self::assertSame(['Other' => 'Some\\Other'], $namespace->getUses());
    }

    public function testUseFunction(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        self::assertEmpty($namespace->getUses(NettePhpNamespace::NameFunction));

        $namespace->addUseFunction('foo');
        $namespace->addUseFunction('bar', 'baz');
        $namespace->addClass('Test')->addMethod('test')->addBody('foo();baz();');

        self::assertSame(['baz' => 'bar', 'foo' => 'foo'], $namespace->getUses(NettePhpNamespace::NameFunction));
        self::assertStringContainsString('use function bar as baz;', $namespace->__toString());
        self::assertStringContainsString('use function foo;', $namespace->__toString());

        $namespace->removeUse('bar', NettePhpNamespace::NameFunction);
        self::assertSame(['foo' => 'foo'], $namespace->getUses(NettePhpNamespace::NameFunction));
    }

    public function testUseConstant(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        self::assertEmpty($namespace->getUses(NettePhpNamespace::NameConstant));

        $namespace->addUseConstant('foo');
        $namespace->addUseConstant('bar', 'baz');
        $namespace->addClass('Test')->addMethod('test')->addBody('foo::some;baz::some;');

        self::assertSame(['baz' => 'bar', 'foo' => 'foo'], $namespace->getUses(NettePhpNamespace::NameConstant));
        self::assertStringContainsString('use const bar as baz;', $namespace->__toString());
        self::assertStringContainsString('use const foo;', $namespace->__toString());

        $namespace->removeUse('bar', NettePhpNamespace::NameConstant);
        self::assertSame(['foo' => 'foo'], $namespace->getUses(NettePhpNamespace::NameConstant));
    }

    public function testRemoveElement(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');
        $namespace->addClass('Test');
        self::assertCount(1, $namespace->getClasses());

        $namespace->removeElement('Test');
        self::assertCount(0, $namespace->getClasses());
    }

    public function testGetClass(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $class = $namespace->addClass('Test');

        self::assertEquals($class, $namespace->getClass('Test'));
    }

    public function testAddClass(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $class = $namespace->addClass('Test');

        self::assertCount(1, $namespace->getClasses());
        self::assertEquals($class, $namespace->getClasses()->getIterator()->current());
    }

    public function testGetInterface(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $interface = $namespace->addInterface('Test');

        self::assertEquals($interface, $namespace->getInterface('Test'));
    }

    public function testAddInterface(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $interface = $namespace->addInterface('Test');

        self::assertCount(1, $namespace->getInterfaces());
        self::assertEquals($interface, $namespace->getInterfaces()->getIterator()->current());
    }

    public function testGetTrait(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $trait = $namespace->addTrait('Test');

        self::assertEquals($trait, $namespace->getTrait('Test'));
    }

    public function testAddTrait(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $trait = $namespace->addTrait('Test');

        self::assertCount(1, $namespace->getTraits());
        self::assertEquals($trait, $namespace->getTraits()->getIterator()->current());
    }

    public function testGetEnum(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $enum = $namespace->addEnum('Test');

        self::assertEquals($enum, $namespace->getEnum('Test'));
    }

    public function testAddEnum(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $enum = $namespace->addEnum('Test');

        self::assertCount(1, $namespace->getEnums());
        self::assertEquals($enum, $namespace->getEnums()->getIterator()->current());
    }

    public function testRender(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');
        $namespace->addClass('Test');

        self::assertStringContainsString('namespace Foo\\Bar;', $namespace->__toString());
    }

    public function testFromElement(): void
    {
        $namespace = PhpNamespace::fromElement(new NettePhpNamespace('Foo\\Bar'));

        self::assertInstanceOf(PhpNamespace::class, $namespace);
        self::assertSame('Foo\\Bar', $namespace->getName());
    }

    public function testGetElement(): void
    {
        $element = (new PhpNamespace('Foo\\Bar'))->getElement();

        self::assertInstanceOf(NettePhpNamespace::class, $element);
        self::assertSame('Foo\\Bar', $element->getName());
    }

    #[DataProvider('classesDataProvider')]
    public function testGetClasses(PhpNamespace $namespace, Classes $expected): void
    {
        self::assertEquals($namespace->getClasses(), $expected);
    }

    #[DataProvider('interfacesDataProvider')]
    public function testGetInterfaces(PhpNamespace $namespace, Interfaces $expected): void
    {
        self::assertEquals($namespace->getInterfaces(), $expected);
    }

    #[DataProvider('traitsDataProvider')]
    public function testGetTraits(PhpNamespace $namespace, Traits $expected): void
    {
        self::assertEquals($namespace->getTraits(), $expected);
    }

    #[DataProvider('enumsDataProvider')]
    public function testGetEnums(PhpNamespace $namespace, Enums $expected): void
    {
        self::assertEquals($namespace->getEnums(), $expected);
    }

    #[DataProvider('elementsDataProvider')]
    public function testGetElements(PhpNamespace $namespace, Elements $expected): void
    {
        self::assertEquals($namespace->getElements(), $expected);
    }

    protected static function getTestedClass(): string
    {
        return PhpNamespace::class;
    }
}
