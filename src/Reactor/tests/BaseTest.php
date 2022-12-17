<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use PHPUnit\Framework\TestCase;
use Spiral\Reactor\Aggregator\Classes;
use Spiral\Reactor\Aggregator\Elements;
use Spiral\Reactor\Aggregator\Enums;
use Spiral\Reactor\Aggregator\Interfaces;
use Spiral\Reactor\Aggregator\Traits;
use Spiral\Reactor\Partial\PhpNamespace;

abstract class BaseTest extends TestCase
{
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
