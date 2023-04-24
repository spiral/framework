<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

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

    public function testGetClass(): void
    {
        $file = new FileDeclaration();
        $class = $file->addClass('Test');

        $this->assertEquals($class, $file->getClass('Test'));
    }

    public function testAddClass(): void
    {
        $file = new FileDeclaration();
        $class = $file->addClass('Test');

        $this->assertCount(1, $file->getClasses());
        $this->assertEquals($class, $file->getClasses()->getIterator()->current());
    }

    public function testGetInterface(): void
    {
        $file = new FileDeclaration();
        $interface = $file->addInterface('Test');

        $this->assertEquals($interface, $file->getInterface('Test'));
    }

    public function testAddInterface(): void
    {
        $file = new FileDeclaration();
        $interface = $file->addInterface('Test');

        $this->assertCount(1, $file->getInterfaces());
        $this->assertEquals($interface, $file->getInterfaces()->getIterator()->current());
    }

    public function testGetTrait(): void
    {
        $file = new FileDeclaration();
        $trait = $file->addTrait('Test');

        $this->assertEquals($trait, $file->getTrait('Test'));
    }

    public function testAddTrait(): void
    {
        $file = new FileDeclaration();
        $trait = $file->addTrait('Test');

        $this->assertCount(1, $file->getTraits());
        $this->assertEquals($trait, $file->getTraits()->getIterator()->current());
    }

    public function testGetEnum(): void
    {
        $file = new FileDeclaration();
        $enum = $file->addEnum('Test');

        $this->assertEquals($enum, $file->getEnum('Test'));
    }

    public function testAddEnum(): void
    {
        $file = new FileDeclaration();
        $enum = $file->addEnum('Test');

        $this->assertCount(1, $file->getEnums());
        $this->assertEquals($enum, $file->getEnums()->getIterator()->current());
    }

    public function testAddNamespace(): void
    {
        $file = new FileDeclaration();

        $this->assertEmpty($file->getNamespaces());

        $namespace = $file->addNamespace('Foo\\Bar');

        $this->assertCount(1, $file->getNamespaces());
        $this->assertEquals($namespace, $file->getNamespaces()->getIterator()->current());
    }

    public function testAddFunction(): void
    {
        $file = new FileDeclaration();

        $this->assertEmpty($file->getFunctions());

        $function = $file->addFunction('test');

        $this->assertCount(1, $file->getFunctions());
        $this->assertEquals($function, $file->getFunctions()->getIterator()->current());
    }

    public function testAddUse(): void
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

    #[DataProvider('classesDataProvider')]
    public function testGetClasses(FileDeclaration $file, Classes $expected): void
    {
        $this->assertEquals($file->getClasses(), $expected);
    }

    #[DataProvider('interfacesDataProvider')]
    public function testGetInterfaces(FileDeclaration $file, Interfaces $expected): void
    {
        $this->assertEquals($file->getInterfaces(), $expected);
    }

    #[DataProvider('traitsDataProvider')]
    public function testGetTraits(FileDeclaration $file, Traits $expected): void
    {
        $this->assertEquals($file->getTraits(), $expected);
    }

    #[DataProvider('enumsDataProvider')]
    public function testGetEnums(FileDeclaration $file, Enums $expected): void
    {
        $this->assertEquals($file->getEnums(), $expected);
    }

    #[DataProvider('elementsDataProvider')]
    public function testGetElements(FileDeclaration $file, Elements $expected): void
    {
        $this->assertEquals($file->getElements(), $expected);
    }

    protected static function getTestedClass(): string
    {
        return FileDeclaration::class;
    }
}
