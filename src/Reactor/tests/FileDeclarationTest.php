<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Reactor\Aggregator\Classes;
use Spiral\Reactor\Aggregator\Elements;
use Spiral\Reactor\Aggregator\Enums;
use Spiral\Reactor\Aggregator\Interfaces;
use Spiral\Reactor\Aggregator\Traits;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\FileDeclaration;

final class FileDeclarationTest extends BaseWithElementsTestCase
{
    public function testFromCode(): void
    {
        $file = FileDeclaration::fromCode('<?php
             namespace Foo\Bar;

             use Baz\Bar\ClassA;
             use Baz\Bar\ClassB;

             final class MyClass implements \Countable
             {
                 public const TEST = [
                     ClassA::class => ClassB::class,
                 ];
             }'
        );

        $class = $file->getClass('MyClass');

        self::assertInstanceOf(FileDeclaration::class, $file);
        self::assertInstanceOf(ClassDeclaration::class, $class);

        self::assertSame('MyClass', $class->getName());
        self::assertTrue($class->isFinal());
        self::assertInstanceOf(Literal::class, $class->getConstant('TEST')->getValue());
        self::assertSame([\Countable::class], $class->getImplements());
    }

    public function testGetClass(): void
    {
        $file = new FileDeclaration();
        $class = $file->addClass('Test');

        self::assertEquals($class, $file->getClass('Test'));
    }

    public function testAddClass(): void
    {
        $file = new FileDeclaration();
        $class = $file->addClass('Test');

        self::assertCount(1, $file->getClasses());
        self::assertEquals($class, $file->getClasses()->getIterator()->current());
    }

    public function testGetInterface(): void
    {
        $file = new FileDeclaration();
        $interface = $file->addInterface('Test');

        self::assertEquals($interface, $file->getInterface('Test'));
    }

    public function testAddInterface(): void
    {
        $file = new FileDeclaration();
        $interface = $file->addInterface('Test');

        self::assertCount(1, $file->getInterfaces());
        self::assertEquals($interface, $file->getInterfaces()->getIterator()->current());
    }

    public function testGetTrait(): void
    {
        $file = new FileDeclaration();
        $trait = $file->addTrait('Test');

        self::assertEquals($trait, $file->getTrait('Test'));
    }

    public function testAddTrait(): void
    {
        $file = new FileDeclaration();
        $trait = $file->addTrait('Test');

        self::assertCount(1, $file->getTraits());
        self::assertEquals($trait, $file->getTraits()->getIterator()->current());
    }

    public function testGetEnum(): void
    {
        $file = new FileDeclaration();
        $enum = $file->addEnum('Test');

        self::assertEquals($enum, $file->getEnum('Test'));
    }

    public function testAddEnum(): void
    {
        $file = new FileDeclaration();
        $enum = $file->addEnum('Test');

        self::assertCount(1, $file->getEnums());
        self::assertEquals($enum, $file->getEnums()->getIterator()->current());
    }

    public function testAddNamespace(): void
    {
        $file = new FileDeclaration();

        self::assertEmpty($file->getNamespaces());

        $namespace = $file->addNamespace('Foo\\Bar');

        self::assertCount(1, $file->getNamespaces());
        self::assertEquals($namespace, $file->getNamespaces()->getIterator()->current());
    }

    public function testAddFunction(): void
    {
        $file = new FileDeclaration();

        self::assertEmpty($file->getFunctions());

        $function = $file->addFunction('test');

        self::assertCount(1, $file->getFunctions());
        self::assertEquals($function, $file->getFunctions()->getIterator()->current());
    }

    public function testAddUse(): void
    {
        $file = new FileDeclaration();
        $file->addUse('Foo\\Bar');
        $file->addClass('Test')->addImplement('Foo\\Bar');

        self::assertStringContainsString('use Foo\\Bar;', (string) $file);
    }

    public function testStrictTypes(): void
    {
        $file = new FileDeclaration();

        self::assertTrue($file->hasStrictTypes());

        $file->setStrictTypes(false);

        self::assertFalse($file->hasStrictTypes());
    }

    public function testFromElement(): void
    {
        $file = FileDeclaration::fromElement(new PhpFile());

        self::assertInstanceOf(FileDeclaration::class, $file);
    }

    public function testGetElement(): void
    {
        $element = (new FileDeclaration())->getElement();

        self::assertInstanceOf(PhpFile::class, $element);
    }

    #[DataProvider('classesDataProvider')]
    public function testGetClasses(FileDeclaration $file, Classes $expected): void
    {
        self::assertEquals($file->getClasses(), $expected);
    }

    #[DataProvider('interfacesDataProvider')]
    public function testGetInterfaces(FileDeclaration $file, Interfaces $expected): void
    {
        self::assertEquals($file->getInterfaces(), $expected);
    }

    #[DataProvider('traitsDataProvider')]
    public function testGetTraits(FileDeclaration $file, Traits $expected): void
    {
        self::assertEquals($file->getTraits(), $expected);
    }

    #[DataProvider('enumsDataProvider')]
    public function testGetEnums(FileDeclaration $file, Enums $expected): void
    {
        self::assertEquals($file->getEnums(), $expected);
    }

    #[DataProvider('elementsDataProvider')]
    public function testGetElements(FileDeclaration $file, Elements $expected): void
    {
        self::assertEquals($file->getElements(), $expected);
    }

    protected static function getTestedClass(): string
    {
        return FileDeclaration::class;
    }
}
