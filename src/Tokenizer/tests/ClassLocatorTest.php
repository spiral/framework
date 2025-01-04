<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer;

use Spiral\Tests\Tokenizer\Classes\ClassA;
use Spiral\Tests\Tokenizer\Classes\ClassB;
use Spiral\Tests\Tokenizer\Classes\ClassC;
use Spiral\Tests\Tokenizer\Classes\Inner\ClassD;
use Spiral\Tests\Tokenizer\Fixtures\TestInterface;
use Spiral\Tests\Tokenizer\Fixtures\TestTrait;

final class ClassLocatorTest extends TestCase
{
    public function testClassesAll(): void
    {
        $tokenizer = $this->getTokenizer();

        //Direct loading
        $classes = $tokenizer->classLocator()->getClasses();

        self::assertArrayHasKey(self::class, $classes);
        self::assertArrayHasKey(ClassA::class, $classes);
        self::assertArrayHasKey(ClassB::class, $classes);
        self::assertArrayHasKey(ClassC::class, $classes);
        self::assertArrayHasKey(ClassD::class, $classes);

        //Excluded
        self::assertArrayNotHasKey(\Spiral\Tests\Tokenizer\Classes\Excluded\ClassXX::class, $classes);
        self::assertArrayNotHasKey('Spiral\Tests\Tokenizer\Classes\Bad_Class', $classes);
    }

    public function testClassesByClass(): void
    {
        $tokenizer = $this->getTokenizer();

        //By namespace
        $classes = $tokenizer->classLocator()->getClasses(ClassD::class);

        self::assertArrayHasKey(ClassD::class, $classes);

        self::assertArrayNotHasKey(self::class, $classes);
        self::assertArrayNotHasKey(ClassA::class, $classes);
        self::assertArrayNotHasKey(ClassB::class, $classes);
        self::assertArrayNotHasKey(ClassC::class, $classes);
    }

    public function testClassesByInterface(): void
    {
        $tokenizer = $this->getTokenizer();

        //By interface
        $classes = $tokenizer->classLocator()->getClasses(TestInterface::class);

        self::assertArrayHasKey(ClassB::class, $classes);
        self::assertArrayHasKey(ClassC::class, $classes);

        self::assertArrayNotHasKey(self::class, $classes);
        self::assertArrayNotHasKey(ClassA::class, $classes);
        self::assertArrayNotHasKey(ClassD::class, $classes);
    }

    public function testClassesByTrait(): void
    {
        $tokenizer = $this->getTokenizer();

        //By trait
        $classes = $tokenizer->classLocator()->getClasses(TestTrait::class);

        self::assertArrayHasKey(ClassB::class, $classes);
        self::assertArrayHasKey(ClassC::class, $classes);

        self::assertArrayNotHasKey(self::class, $classes);
        self::assertArrayNotHasKey(ClassA::class, $classes);
        self::assertArrayNotHasKey(ClassD::class, $classes);
    }

    public function testClassesByClassA(): void
    {
        $tokenizer = $this->getTokenizer();

        //By class
        $classes = $tokenizer->classLocator()->getClasses(ClassA::class);

        self::assertArrayHasKey(ClassA::class, $classes);
        self::assertArrayHasKey(ClassB::class, $classes);
        self::assertArrayHasKey(ClassC::class, $classes);
        self::assertArrayHasKey(ClassD::class, $classes);

        self::assertArrayNotHasKey(self::class, $classes);
    }

    public function testClassesByClassB(): void
    {
        $tokenizer = $this->getTokenizer();
        $classes = $tokenizer->classLocator()->getClasses(ClassB::class);

        self::assertArrayHasKey(ClassB::class, $classes);
        self::assertArrayHasKey(ClassC::class, $classes);

        self::assertArrayNotHasKey(self::class, $classes);
        self::assertArrayNotHasKey(ClassA::class, $classes);
        self::assertArrayNotHasKey(ClassD::class, $classes);
    }
}
