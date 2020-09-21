<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\ClassNode\ConflictResolver;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Prototype\ClassNode;
use Spiral\Prototype\Exception\ClassNotDeclaredException;
use Spiral\Prototype\Injector;
use Spiral\Prototype\NodeExtractor;
use Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures;
use Spiral\Tests\Prototype\Fixtures\Dependencies;

class ConflictResolverTest extends TestCase
{
    /**
     * @throws ClassNotDeclaredException
     */
    public function testResolveInternalConflicts(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/Fixtures/TestClass.php';
        $r = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition($filename, [
                'test'  => Fixtures\Test::class,
                'test2' => Fixtures\SubFolder\Test::class,
                'test3' => Fixtures\ATest3::class,
            ])
        );

        $this->assertStringContainsString(Fixtures\Test::class . ';', $r);
        $this->assertRegExp('/@var Test[\s|\r\n]/', $r);
        $this->assertStringContainsString('@param Test $test', $r);

        $this->assertStringContainsString(Fixtures\SubFolder\Test::class . ' as Test2;', $r);
        $this->assertStringNotContainsString(Fixtures\SubFolder\Test::class . ';', $r);
        $this->assertRegExp('/@var Test2[\s|\r\n]/', $r);
        $this->assertStringContainsString('@param Test2 $test2', $r);

        $this->assertStringContainsString(Fixtures\ATest3::class . ';', $r);
        $this->assertRegExp('/@var ATest3[\s|\r\n]/', $r);
        $this->assertStringContainsString('@param ATest3 $test3', $r);
    }

    /**
     * @param string $filename
     * @param array $dependencies
     *
     * @return ClassNode
     * @throws ClassNotDeclaredException|\ReflectionException
     */
    private function getDefinition(string $filename, array $dependencies): ClassNode
    {
        return $this->getExtractor()->extract($filename, Dependencies::convert($dependencies));
    }

    private function getExtractor(): NodeExtractor
    {
        $container = new Container();

        return $container->get(NodeExtractor::class);
    }

    /**
     * @throws ClassNotDeclaredException
     */
    public function testResolveImportConflicts(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/Fixtures/TestClassWithImports.php';
        $r = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition($filename, [
                'test'  => Fixtures\Test::class,
                'test2' => Fixtures\SubFolder\Test::class,
                'test3' => Fixtures\ATest3::class,
            ])
        );

        $this->assertStringContainsString(Fixtures\Test::class . ' as FTest;', $r);
        $this->assertStringNotContainsString(Fixtures\Test::class . ';', $r);
        $this->assertRegExp('/@var FTest[\s|\r\n]/', $r);
        $this->assertStringContainsString('@param FTest $test', $r);

        $this->assertStringContainsString(Fixtures\SubFolder\Test::class . ' as TestAlias;', $r);
        $this->assertStringNotContainsString(Fixtures\SubFolder\Test::class . ';', $r);
        $this->assertRegExp('/@var TestAlias[\s|\r\n]/', $r);
        $this->assertStringContainsString('@param TestAlias $test2', $r);

        $this->assertStringContainsString(Fixtures\ATest3::class . ' as ATest;', $r);
        $this->assertStringNotContainsString(Fixtures\ATest3::class . ';', $r);
        $this->assertRegExp('/@var ATest[\s|\r\n]/', $r);
        $this->assertStringContainsString('@param ATest $test3', $r);
    }

    /**
     * @throws ClassNotDeclaredException
     */
    public function testResolveWithAliasForParentConstructor(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/Fixtures/ChildClass.php';
        $r = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition($filename, [
                'test'  => Fixtures\Test::class,
                'test2' => Fixtures\SubFolder\Test::class,
                'test3' => Fixtures\ATest3::class,
            ])
        );

        $this->assertStringContainsString(Fixtures\Test::class . ';', $r);
        $this->assertRegExp('/@var Test[\s|\r\n]/', $r);
        $this->assertStringContainsString('@param Test $test', $r);

        $this->assertStringContainsString(Fixtures\SubFolder\Test::class . ' as Test2;', $r);
        $this->assertStringNotContainsString(Fixtures\SubFolder\Test::class . ';', $r);
        $this->assertRegExp('/@var Test2[\s|\r\n]/', $r);
        $this->assertStringContainsString('@param Test2 $test2', $r);

        $this->assertStringContainsString(Fixtures\ATest3::class . ' as ATestAlias;', $r);
        $this->assertStringNotContainsString(Fixtures\ATest3::class . ';', $r);
        $this->assertRegExp('/@var ATestAlias[\s|\r\n]/', $r);
        $this->assertStringContainsString('@param ATestAlias $test3', $r);
    }
}
