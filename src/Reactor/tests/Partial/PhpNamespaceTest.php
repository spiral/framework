<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor\Partial;

use PHPUnit\Framework\TestCase;
use Nette\PhpGenerator\PhpNamespace as NettePhpNamespace;
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

        $namespace->removeClass('Test');
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
}
