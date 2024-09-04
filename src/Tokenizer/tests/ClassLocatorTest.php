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

        $this->assertArrayHasKey(self::class, $classes);
        $this->assertArrayHasKey(ClassA::class, $classes);
        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);
        $this->assertArrayHasKey(ClassD::class, $classes);

        //Excluded
        $this->assertArrayNotHasKey(\Spiral\Tests\Tokenizer\Classes\Excluded\ClassXX::class, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Classes\Bad_Class', $classes);
    }

    public function testClassesByClass(): void
    {
        $tokenizer = $this->getTokenizer();

        //By namespace
        $classes = $tokenizer->classLocator()->getClasses(ClassD::class);

        $this->assertArrayHasKey(ClassD::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
        $this->assertArrayNotHasKey(ClassA::class, $classes);
        $this->assertArrayNotHasKey(ClassB::class, $classes);
        $this->assertArrayNotHasKey(ClassC::class, $classes);
    }

    public function testClassesByInterface(): void
    {
        $tokenizer = $this->getTokenizer();

        //By interface
        $classes = $tokenizer->classLocator()->getClasses(TestInterface::class);

        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
        $this->assertArrayNotHasKey(ClassA::class, $classes);
        $this->assertArrayNotHasKey(ClassD::class, $classes);
    }

    public function testClassesByTrait(): void
    {
        $tokenizer = $this->getTokenizer();

        //By trait
        $classes = $tokenizer->classLocator()->getClasses(TestTrait::class);

        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
        $this->assertArrayNotHasKey(ClassA::class, $classes);
        $this->assertArrayNotHasKey(ClassD::class, $classes);
    }

    public function testClassesByClassA(): void
    {
        $tokenizer = $this->getTokenizer();

        //By class
        $classes = $tokenizer->classLocator()->getClasses(ClassA::class);

        $this->assertArrayHasKey(ClassA::class, $classes);
        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);
        $this->assertArrayHasKey(ClassD::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
    }

    public function testClassesByClassB(): void
    {
        $tokenizer = $this->getTokenizer();
        $classes = $tokenizer->classLocator()->getClasses(ClassB::class);

        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
        $this->assertArrayNotHasKey(ClassA::class, $classes);
        $this->assertArrayNotHasKey(ClassD::class, $classes);
    }
}
