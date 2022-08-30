<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Annotations;

use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Spiral\Annotations\AnnotationLocator;
use Spiral\Attributes\AnnotationReader;
use Spiral\Attributes\AttributeReader;
use Spiral\Tests\Annotations\Fixtures\Annotation\PropertyAnnotation;
use Spiral\Tests\Annotations\Fixtures\Annotation\MethodAnnotation;
use Spiral\Tests\Annotations\Fixtures\Annotation\ClassAnnotation;
use Spiral\Tests\Annotations\Fixtures\AttributeTestClass;
use Spiral\Tests\Annotations\Fixtures\TestClass;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;

class LocatorTest extends TestCase
{
    public function testLocateClasses()
    {
        // Annotations.
        $classes = $this->getAnnotationsLocator(__DIR__ . '/Fixtures')->findClasses(ClassAnnotation::class);
        $classes = iterator_to_array($classes);

        $this->assertCount(1, $classes);

        foreach ($classes as $class) {
            $this->assertSame(TestClass::class, $class->getClass()->getName());
            $this->assertSame('abc', $class->getAnnotation()->value);
        }

        // Attributes.
        $classes = $this->getAttributesLocator(__DIR__ . '/Fixtures')->findClasses(ClassAnnotation::class);
        $classes = iterator_to_array($classes);

        $this->assertCount(1, $classes);

        foreach ($classes as $class) {
            $this->assertSame(AttributeTestClass::class, $class->getClass()->getName());
            $this->assertSame('abc', $class->getAnnotation()->value);
        }
    }

    public function testLocateProperties()
    {
        // Annotations.
        $props = $this->getAnnotationsLocator(__DIR__ . '/Fixtures')->findProperties(PropertyAnnotation::class);
        $props = iterator_to_array($props);

        $this->assertCount(1, $props);

        foreach ($props as $prop) {
            $this->assertSame(TestClass::class, $prop->getClass()->getName());
            $this->assertSame('name', $prop->getProperty()->getName());
            $this->assertSame('123', $prop->getAnnotation()->id);
        }

        // Attributes.
        $props = $this->getAttributesLocator(__DIR__ . '/Fixtures')->findProperties(PropertyAnnotation::class);
        $props = iterator_to_array($props);

        $this->assertCount(1, $props);

        foreach ($props as $prop) {
            $this->assertSame(AttributeTestClass::class, $prop->getClass()->getName());
            $this->assertSame('name', $prop->getProperty()->getName());
            $this->assertSame('123', $prop->getAnnotation()->id);
        }
    }

    public function testLocateMethods()
    {
        // Annotations.
        $methods = $this->getAnnotationsLocator(__DIR__ . '/Fixtures')->findMethods(MethodAnnotation::class);
        $methods = iterator_to_array($methods);

        $this->assertCount(1, $methods);

        foreach ($methods as $m) {
            $this->assertSame(TestClass::class, $m->getClass()->getName());
            $this->assertSame('testMethod', $m->getMethod()->getName());
            $this->assertSame('/', $m->getAnnotation()->path);
        }

        // Attributes.
        $methods = $this->getAttributesLocator(__DIR__ . '/Fixtures')->findMethods(MethodAnnotation::class);
        $methods = iterator_to_array($methods);

        $this->assertCount(1, $methods);

        foreach ($methods as $m) {
            $this->assertSame(AttributeTestClass::class, $m->getClass()->getName());
            $this->assertSame('testMethod', $m->getMethod()->getName());
            $this->assertSame('/', $m->getAnnotation()->path);
        }
    }

    private function getAnnotationsLocator(string $directory): AnnotationLocator
    {
        AnnotationRegistry::registerLoader('class_exists');

        return new AnnotationLocator(
            (new ClassLocator((new Finder())->files()->in([$directory]))),
            new AnnotationReader()
        );
    }

    private function getAttributesLocator(string $directory): AnnotationLocator
    {
        return new AnnotationLocator(
            (new ClassLocator((new Finder())->files()->in([$directory]))),
            new AttributeReader()
        );
    }
}
