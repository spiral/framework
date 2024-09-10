<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\ClassNode\ConflictResolver;

use Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures\Some;
use Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures\ATest3;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Prototype\ClassNode;
use Spiral\Prototype\Injector;
use Spiral\Prototype\NodeExtractor;
use Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures;
use Spiral\Tests\Prototype\Fixtures\Dependencies;

class ConflictResolverTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testResolveInternalConflicts(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/Fixtures/TestClass.php';
        $r = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition(
                $filename,
                [
                    'test'  => Some::class,
                    'test2' => Fixtures\SubFolder\Some::class,
                    'test3' => ATest3::class,
                ]
            )
        );

        $this->assertStringContainsString(Some::class . ';', $r);
        $this->assertStringContainsString(Fixtures\SubFolder\Some::class . ' as Some2;', $r);
        $this->assertStringNotContainsString(Fixtures\SubFolder\Some::class . ';', $r);
        $this->assertStringContainsString(ATest3::class . ';', $r);
    }

    /**
     * @throws \Throwable
     */
    public function testResolveImportConflicts(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/Fixtures/TestClassWithImports.php';
        $r = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition(
                $filename,
                [
                    'test'  => Some::class,
                    'test2' => Fixtures\SubFolder\Some::class,
                    'test3' => ATest3::class,
                ]
            )
        );

        $this->assertStringContainsString(Some::class . ' as FTest;', $r);
        $this->assertStringNotContainsString(Some::class . ';', $r);
        $this->assertStringContainsString(Fixtures\SubFolder\Some::class . ' as TestAlias;', $r);
        $this->assertStringNotContainsString(Fixtures\SubFolder\Some::class . ';', $r);
        $this->assertStringContainsString(ATest3::class . ' as ATest;', $r);
        $this->assertStringNotContainsString(ATest3::class . ';', $r);
    }

    /**
     * @throws \Throwable
     */
    public function testResolveWithAliasForParentConstructor(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/Fixtures/ChildClass.php';
        $r = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition(
                $filename,
                [
                    'test'  => Some::class,
                    'test2' => Fixtures\SubFolder\Some::class,
                    'test3' => ATest3::class,
                ]
            )
        );

        $this->assertStringContainsString(Some::class . ';', $r);
        $this->assertStringContainsString(Fixtures\SubFolder\Some::class . ' as Some2;', $r);
        $this->assertStringNotContainsString(Fixtures\SubFolder\Some::class . ';', $r);
        $this->assertStringContainsString(ATest3::class . ' as ATestAlias;', $r);
        $this->assertStringNotContainsString(ATest3::class . ';', $r);
    }

    public function testDuplicateProperty(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/Fixtures/DuplicatePropertyClass.php';
        $r = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition(
                $filename,
                [
                    'test' => Some::class,
                ]
            )
        );

        $this->assertStringContainsString(Some::class . ';', $r);
        $this->assertStringContainsString('__construct(private readonly Some $test)', $r);
    }

    /**
     *
     * @throws \Throwable
     */
    private function getDefinition(string $filename, array $dependencies): ClassNode
    {
        return $this->getExtractor()->extract($filename, Dependencies::convert($dependencies));
    }

    /**
     * @throws \Throwable
     */
    private function getExtractor(): NodeExtractor
    {
        $container = new Container();

        return $container->get(NodeExtractor::class);
    }
}
