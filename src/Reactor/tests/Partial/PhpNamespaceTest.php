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

    public function testAddUse(): void
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

    public function testRemoveElement(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');
        $namespace->addClass('Test');
        $this->assertCount(1, $namespace->getClasses());

        $namespace->removeElement('Test');
        $this->assertCount(0, $namespace->getClasses());
    }

    public function testGetClass(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $class = $namespace->addClass('Test');

        $this->assertEquals($class, $namespace->getClass('Test'));
    }

    public function testAddClass(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $class = $namespace->addClass('Test');

        $this->assertCount(1, $namespace->getClasses());
        $this->assertEquals($class, $namespace->getClasses()->getIterator()->current());
    }

    public function testGetInterface(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $interface = $namespace->addInterface('Test');

        $this->assertEquals($interface, $namespace->getInterface('Test'));
    }

    public function testAddInterface(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $interface = $namespace->addInterface('Test');

        $this->assertCount(1, $namespace->getInterfaces());
        $this->assertEquals($interface, $namespace->getInterfaces()->getIterator()->current());
    }

    public function testGetTrait(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $trait = $namespace->addTrait('Test');

        $this->assertEquals($trait, $namespace->getTrait('Test'));
    }

    public function testAddTrait(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $trait = $namespace->addTrait('Test');

        $this->assertCount(1, $namespace->getTraits());
        $this->assertEquals($trait, $namespace->getTraits()->getIterator()->current());
    }

    public function testGetEnum(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $enum = $namespace->addEnum('Test');

        $this->assertEquals($enum, $namespace->getEnum('Test'));
    }

    public function testAddEnum(): void
    {
        $namespace = new PhpNamespace('Foo\\Bar');

        $enum = $namespace->addEnum('Test');

        $this->assertCount(1, $namespace->getEnums());
        $this->assertEquals($enum, $namespace->getEnums()->getIterator()->current());
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

    #[DataProvider('classesDataProvider')]
    public function testGetClasses(PhpNamespace $namespace, Classes $expected): void
    {
        $this->assertEquals($namespace->getClasses(), $expected);
    }

    #[DataProvider('interfacesDataProvider')]
    public function testGetInterfaces(PhpNamespace $namespace, Interfaces $expected): void
    {
        $this->assertEquals($namespace->getInterfaces(), $expected);
    }

    #[DataProvider('traitsDataProvider')]
    public function testGetTraits(PhpNamespace $namespace, Traits $expected): void
    {
        $this->assertEquals($namespace->getTraits(), $expected);
    }

    #[DataProvider('enumsDataProvider')]
    public function testGetEnums(PhpNamespace $namespace, Enums $expected): void
    {
        $this->assertEquals($namespace->getEnums(), $expected);
    }

    #[DataProvider('elementsDataProvider')]
    public function testGetElements(PhpNamespace $namespace, Elements $expected): void
    {
        $this->assertEquals($namespace->getElements(), $expected);
    }

    protected static function getTestedClass(): string
    {
        return PhpNamespace::class;
    }
}
