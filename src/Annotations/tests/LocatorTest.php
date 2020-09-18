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
use Spiral\Tests\Annotations\Fixtures\Annotation\Another;
use Spiral\Tests\Annotations\Fixtures\Annotation\Route;
use Spiral\Tests\Annotations\Fixtures\Annotation\Value;
use Spiral\Tests\Annotations\Fixtures\TestClass;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;

class LocatorTest extends TestCase
{
    public function testLocateClasses()
    {
        $classes = $this->getLocator(__DIR__ . '/Fixtures')->findClasses(Value::class);
        $classes = iterator_to_array($classes);

        $this->assertCount(1, $classes);

        foreach ($classes as $class) {
            $this->assertSame(TestClass::class, $class->getClass()->getName());
            $this->assertSame('abc', $class->getAnnotation()->value);
        }
    }

    public function testLocateProperties()
    {
        $props = $this->getLocator(__DIR__ . '/Fixtures')->findProperties(Another::class);
        $props = iterator_to_array($props);

        $this->assertCount(1, $props);

        foreach ($props as $prop) {
            $this->assertSame(TestClass::class, $prop->getClass()->getName());
            $this->assertSame('name', $prop->getProperty()->getName());
            $this->assertSame('123', $prop->getAnnotation()->id);
        }
    }

    public function testLocateMethods()
    {
        $methods = $this->getLocator(__DIR__ . '/Fixtures')->findMethods(Route::class);
        $methods = iterator_to_array($methods);

        $this->assertCount(1, $methods);

        foreach ($methods as $m) {
            $this->assertSame(TestClass::class, $m->getClass()->getName());
            $this->assertSame('testMethod', $m->getMethod()->getName());
            $this->assertSame('/', $m->getAnnotation()->path);
        }
    }

    private function getLocator(string $directory): AnnotationLocator
    {
        AnnotationRegistry::registerLoader('class_exists');

        return new AnnotationLocator(
            (new ClassLocator((new Finder())->files()->in([$directory])))
        );
    }
}
