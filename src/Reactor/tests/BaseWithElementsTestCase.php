<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use PHPUnit\Framework\TestCase;
use Spiral\Reactor\Aggregator\Classes;
use Spiral\Reactor\Aggregator\Elements;
use Spiral\Reactor\Aggregator\Enums;
use Spiral\Reactor\Aggregator\Interfaces;
use Spiral\Reactor\Aggregator\Traits;

abstract class BaseWithElementsTestCase extends TestCase
{
    public static function classesDataProvider(): \Traversable
    {
        $testedClass = static::getTestedClass();

        $withoutClasses = new $testedClass('a');
        $withoutClasses->addInterface('b');
        $withoutClasses->addTrait('c');
        $withoutClasses->addEnum('d');

        $onlyOneClass = new $testedClass('b');
        $a = $onlyOneClass->addClass('a');

        $onlyClasses = new $testedClass('c');
        $b = $onlyClasses->addClass('b');
        $c = $onlyClasses->addClass('c');

        $withOtherElements = new $testedClass('d');
        $d = $withOtherElements->addClass('d');
        $withOtherElements->addInterface('b');
        $withOtherElements->addTrait('c');
        $withOtherElements->addEnum('l');

        yield [new $testedClass('a'), new Classes([])];
        yield [$withoutClasses, new Classes([])];
        yield [$onlyOneClass, new Classes(['a' => $a])];
        yield [$onlyClasses, new Classes(['b' => $b, 'c' => $c])];
        yield [$withOtherElements, new Classes(['d' => $d])];
    }

    public static function interfacesDataProvider(): \Traversable
    {
        $testedClass = static::getTestedClass();

        $withoutInterfaces = new $testedClass('a');
        $withoutInterfaces->addClass('b');
        $withoutInterfaces->addTrait('c');
        $withoutInterfaces->addEnum('d');

        $onlyOneInterface = new $testedClass('b');
        $a = $onlyOneInterface->addInterface('a');

        $onlyInterfaces = new $testedClass('c');
        $b = $onlyInterfaces->addInterface('b');
        $c = $onlyInterfaces->addInterface('c');

        $withOtherElements = new $testedClass('d');
        $withOtherElements->addClass('j');
        $d = $withOtherElements->addInterface('d');
        $withOtherElements->addTrait('l');
        $withOtherElements->addEnum('k');

        yield [new $testedClass('a'), new Interfaces([])];
        yield [$withoutInterfaces, new Interfaces([])];
        yield [$onlyOneInterface, new Interfaces(['a' => $a])];
        yield [$onlyInterfaces, new Interfaces(['b' => $b, 'c' => $c])];
        yield [$withOtherElements, new Interfaces(['d' => $d])];
    }

    public static function traitsDataProvider(): \Traversable
    {
        $testedClass = static::getTestedClass();

        $withoutTraits = new $testedClass('a');
        $withoutTraits->addClass('b');
        $withoutTraits->addInterface('c');
        $withoutTraits->addEnum('d');

        $onlyOneTrait = new $testedClass('b');
        $a = $onlyOneTrait->addTrait('a');

        $onlyTraits = new $testedClass('c');
        $b = $onlyTraits->addTrait('b');
        $c = $onlyTraits->addTrait('c');

        $withOtherElements = new $testedClass('d');
        $withOtherElements->addClass('a');
        $withOtherElements->addInterface('f');
        $d = $withOtherElements->addTrait('d');
        $withOtherElements->addEnum('l');

        yield [new $testedClass('a'), new Traits([])];
        yield [$withoutTraits, new Traits([])];
        yield [$onlyOneTrait, new Traits(['a' => $a])];
        yield [$onlyTraits, new Traits(['b' => $b, 'c' => $c])];
        yield [$withOtherElements, new Traits(['d' => $d])];
    }

    public static function enumsDataProvider(): \Traversable
    {
        $testedClass = static::getTestedClass();

        $withoutEnums = new $testedClass('a');
        $withoutEnums->addClass('b');
        $withoutEnums->addInterface('c');
        $withoutEnums->addTrait('d');

        $onlyOneEnum = new $testedClass('b');
        $a = $onlyOneEnum->addEnum('a');

        $onlyEnums = new $testedClass('c');
        $b = $onlyEnums->addEnum('b');
        $c = $onlyEnums->addEnum('c');

        $withOtherElements = new $testedClass('d');
        $withOtherElements->addClass('a');
        $withOtherElements->addInterface('b');
        $withOtherElements->addTrait('c');
        $d = $withOtherElements->addEnum('d');

        yield [new $testedClass('a'), new Enums([])];
        yield [$withoutEnums, new Enums([])];
        yield [$onlyOneEnum, new Enums(['a' => $a])];
        yield [$onlyEnums, new Enums(['b' => $b, 'c' => $c])];
        yield [$withOtherElements, new Enums(['d' => $d])];
    }

    public static function elementsDataProvider(): \Traversable
    {
        $testedClass = static::getTestedClass();

        $class = new $testedClass('a');
        $a = $class->addClass('a');

        $interface = new $testedClass('a');
        $b = $interface->addInterface('b');

        $trait = new $testedClass('a');
        $c = $trait->addTrait('c');

        $enum = new $testedClass('a');
        $d = $enum->addEnum('d');

        $all = new $testedClass('a');
        $e = $all->addEnum('e');
        $f = $all->addClass('f');
        $g = $all->addInterface('g');
        $h = $all->addTrait('h');

        yield [new $testedClass('a'), new Elements([])];
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

    abstract protected static function getTestedClass(): string;
}
