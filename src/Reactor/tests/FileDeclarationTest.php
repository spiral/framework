<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use Nette\PhpGenerator\PhpFile;
use PHPUnit\Framework\TestCase;
use Spiral\Reactor\Aggregator\Classes;
use Spiral\Reactor\Aggregator\Functions;
use Spiral\Reactor\Aggregator\Namespaces;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\EnumDeclaration;
use Spiral\Reactor\FileDeclaration;
use Spiral\Reactor\FunctionDeclaration;
use Spiral\Reactor\InterfaceDeclaration;
use Spiral\Reactor\Partial\PhpNamespace;
use Spiral\Reactor\TraitDeclaration;

final class FileDeclarationTest extends TestCase
{
    public function testFromCode(): void
    {
        $file = FileDeclaration::fromCode('<?php
             final class MyClass implements Countable
             {
             }'
        );

        $class = $file->getClass('MyClass');

        $this->assertInstanceOf(FileDeclaration::class, $file);
        $this->assertInstanceOf(ClassDeclaration::class, $class);

        $this->assertSame('MyClass', $class->getName());
        $this->assertTrue($class->isFinal());
        $this->assertSame([\Countable::class], $class->getImplements());
    }

    public function testClass(): void
    {
        $file = new FileDeclaration();
        $file->addClass('Test');

        $this->assertInstanceOf(ClassDeclaration::class, $file->getClass('Test'));

        $this->assertSame('Test', $file->getClass('Test')->getName());
        $this->assertCount(1, $file->getClasses());
        $this->assertInstanceOf(Classes::class, $file->getClasses());
    }

    public function testInterface(): void
    {
        $file = new FileDeclaration();
        $interface = $file->addInterface('Test');

        $this->assertInstanceOf(InterfaceDeclaration::class, $interface);
        $this->assertSame('Test', $interface->getName());
    }

    public function testTrait(): void
    {
        $file = new FileDeclaration();
        $trait = $file->addTrait('Test');

        $this->assertInstanceOf(TraitDeclaration::class, $trait);
        $this->assertSame('Test', $trait->getName());
    }

    public function testEnum(): void
    {
        $file = new FileDeclaration();
        $enum = $file->addEnum('Test');

        $this->assertInstanceOf(EnumDeclaration::class, $enum);
        $this->assertSame('Test', $enum->getName());
    }

    public function testNamespace(): void
    {
        $file = new FileDeclaration();

        $this->assertEmpty($file->getNamespaces());

        $namespace = $file->addNamespace('Foo\\Bar');

        $this->assertInstanceOf(PhpNamespace::class, $namespace);
        $this->assertSame('Foo\\Bar', $namespace->getName());

        $this->assertCount(1, $file->getNamespaces());
        $this->assertInstanceOf(Namespaces::class, $file->getNamespaces());
    }

    public function testFunction(): void
    {
        $file = new FileDeclaration();

        $this->assertEmpty($file->getFunctions());

        $function = $file->addFunction('test');

        $this->assertInstanceOf(FunctionDeclaration::class, $function);
        $this->assertSame('test', $function->getName());

        $this->assertCount(1, $file->getFunctions());
        $this->assertInstanceOf(Functions::class, $file->getFunctions());
    }

    public function testUse(): void
    {
        $file = new FileDeclaration();
        $file->addUse('Foo\\Bar');

        $this->assertStringContainsString('use Foo\\Bar;', (string) $file);
    }

    public function testStrictTypes(): void
    {
        $file = new FileDeclaration();

        $this->assertTrue($file->hasStrictTypes());

        $file->setStrictTypes(false);

        $this->assertFalse($file->hasStrictTypes());
    }

    public function testFromElement(): void
    {
        $file = FileDeclaration::fromElement(new PhpFile());

        $this->assertInstanceOf(FileDeclaration::class, $file);
    }

    public function testGetElement(): void
    {
        $element = (new FileDeclaration())->getElement();

        $this->assertInstanceOf(PhpFile::class, $element);
    }
}
