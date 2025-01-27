<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Prototype\ClassNode;
use Spiral\Prototype\Exception\ClassNotDeclaredException;
use Spiral\Prototype\Injector;
use Spiral\Prototype\NodeExtractor;
use Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures as ResolverFixtures;
use Spiral\Tests\Prototype\Fixtures\Dependencies;
use Spiral\Tests\Prototype\Fixtures\InheritedInjection\InjectionTwo;
use Spiral\Tests\Prototype\Fixtures\TestClass;

class InjectorTest extends TestCase
{
    protected function setUp(): void
    {
        if ((string)ini_get('zend.assertions') === 1) {
            ini_set('zend.assertions', 0);
        }
    }

    /**
     * @throws \ReflectionException
     * @throws ClassNotDeclaredException
     */
    public function testSimpleInjection(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/Fixtures/TestClass.php';
        $printed = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition($filename, ['testClass' => TestClass::class])
        );

        self::assertStringContainsString('private readonly TestClass $testClass', $printed);
    }

    public function testPromotedParamInjection(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/Fixtures/WithPromotedProperty.php';
        $printed = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition($filename, ['two' => InjectionTwo::class])
        );

        self::assertStringContainsString('__construct(private readonly InjectionTwo $two, string $foo, private InjectionOne $one)', $printed);
    }

    /**
     * @throws \ReflectionException
     * @throws ClassNotDeclaredException
     */
    public function testEmptyInjection(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/Fixtures/TestEmptyClass.php';
        $content = file_get_contents($filename);
        $printed = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition($filename, [])
        );

        self::assertEquals($content, $printed);
    }

    /**
     * @throws \ReflectionException
     * @throws ClassNotDeclaredException
     */
    public function testTraitRemove(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/Fixtures/TestClass.php';
        $r = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition($filename, ['testClass' => TestClass::class])
        );

        self::assertStringContainsString('use PrototypeTrait;', $r);

        $r = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition($filename, ['testClass' => TestClass::class]),
            true
        );

        self::assertStringNotContainsString('use PrototypeTrait;', $r);
    }

    /**
     * @throws \ReflectionException
     * @throws ClassNotDeclaredException
     */
    public function testParentConstructorCallInjection(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/Fixtures/ChildClass.php';
        $r = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition($filename, ['testClass' => TestClass::class])
        );

        self::assertStringContainsString(TestClass::class, $r);
        self::assertStringContainsString('parent::__construct(', $r);
    }

    /**
     * @throws \ReflectionException
     * @throws ClassNotDeclaredException
     */
    public function testNoParentConstructorCallInjection(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/Fixtures/ChildWithConstructorClass.php';
        $r = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition($filename, ['testClass' => TestClass::class])
        );

        self::assertStringContainsString(TestClass::class, $r);
        self::assertStringNotContainsString('parent::__construct(', $r);
    }

    /**
     * @throws \ReflectionException
     * @throws ClassNotDeclaredException
     */
    public function testModifyConstructor(): void
    {
        $filename = __DIR__ . '/Fixtures/WithConstructor.php';
        $extractor = new Traverse\Extractor();

        $parameters = $extractor->extractFromFilename($filename);
        self::assertArrayNotHasKey('testClass', $parameters);

        $i = new Injector();

        $printed = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition($filename, ['testClass' => TestClass::class])
        );

        self::assertStringContainsString('@param HydratedClass $h', $printed);
        self::assertStringContainsString('private readonly TestClass $testClass', $printed);

        $parameters = $extractor->extractFromString($printed);
        self::assertArrayHasKey('testClass', $parameters);
    }

    /**
     * @throws \ReflectionException
     * @throws ClassNotDeclaredException
     */
    public function testPriorOptionalConstructorParameters(): void
    {
        $filename = __DIR__ . '/Fixtures/OptionalConstructorArgsClass.php';
        $extractor = new Traverse\Extractor();

        $parameters = $extractor->extractFromFilename($filename);
        self::assertArrayNotHasKey('testClass', $parameters);

        $i = new Injector();

        $printed = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition($filename, ['testClass' => TestClass::class])
        );

        $parameters = $extractor->extractFromString($printed);
        self::assertSame(['testClass', 'a', 'b', 'c', 'd', 'e'], array_keys($parameters));

        self::assertFalse($parameters['a']['optional']);
        self::assertFalse($parameters['b']['optional']);
        self::assertTrue($parameters['c']['optional']);
        self::assertTrue($parameters['d']['optional']);
        self::assertTrue($parameters['e']['optional']);
    }

    /**
     * @throws \ReflectionException
     * @throws ClassNotDeclaredException
     */
    public function testParentConstructorParamsTypeDefinition(): void
    {
        $i = new Injector();

        $filename = __DIR__ . '/ClassNode/ConflictResolver/Fixtures/ChildClass.php';
        $printed = $i->injectDependencies(
            file_get_contents($filename),
            $this->getDefinition(
                $filename,
                [
                    'test'  => ResolverFixtures\Some::class,
                    'test2' => ResolverFixtures\SubFolder\Some::class,
                    'test3' => ResolverFixtures\ATest3::class,
                ]
            )
        );

        $extractor = new Traverse\Extractor();
        $parameters = $extractor->extractFromString($printed);

        self::assertArrayHasKey('str1', $parameters);
        self::assertEquals('string', $parameters['str1']['type']);
        self::assertFalse($parameters['str1']['optional']);
        self::assertFalse($parameters['str1']['byRef']);
        self::assertFalse($parameters['str1']['variadic']);

        self::assertArrayHasKey('var', $parameters);
        self::assertNull($parameters['var']['type']);
        self::assertFalse($parameters['var']['optional']);

        self::assertArrayHasKey('untypedVarWithDefault', $parameters);
        self::assertNull($parameters['untypedVarWithDefault']['type']);
        self::assertTrue($parameters['untypedVarWithDefault']['optional']);

        self::assertArrayHasKey('refVar', $parameters);
        self::assertNull($parameters['refVar']['type']);
        self::assertFalse($parameters['refVar']['optional']);
        self::assertTrue($parameters['refVar']['byRef']);
        self::assertFalse($parameters['refVar']['variadic']);

        //Parameter type ATest3 has an alias in a child class
        self::assertArrayHasKey('testApp', $parameters);
        self::assertEquals('ATestAlias', $parameters['testApp']['type']);
        self::assertFalse($parameters['testApp']['optional']);

        self::assertArrayHasKey('str2', $parameters);
        self::assertEquals('?string', $parameters['str2']['type']);
        self::assertFalse($parameters['str2']['optional']);

        //We do not track leading "\" in the class name here
        self::assertArrayHasKey('nullableClass1', $parameters);
        self::assertEquals('?StdClass', $parameters['nullableClass1']['type']);
        self::assertFalse($parameters['nullableClass1']['optional']);

        self::assertArrayHasKey('test1', $parameters);
        self::assertEquals('?Some', $parameters['test1']['type']);
        self::assertTrue($parameters['test1']['optional']);

        self::assertArrayHasKey('str3', $parameters);
        self::assertEquals('?string', $parameters['str3']['type']);
        self::assertTrue($parameters['str3']['optional']);

        self::assertArrayHasKey('int', $parameters);
        self::assertEquals('?int', $parameters['int']['type']);
        self::assertTrue($parameters['int']['optional']);

        self::assertArrayHasKey('nullableClass2', $parameters);
        self::assertEquals('?StdClass', $parameters['nullableClass2']['type']);
        self::assertTrue($parameters['nullableClass2']['optional']);

        self::assertArrayHasKey('variadicVar', $parameters);
        self::assertEquals('string', $parameters['variadicVar']['type']);
        self::assertFalse($parameters['variadicVar']['optional']);
        self::assertFalse($parameters['variadicVar']['byRef']);
        self::assertTrue($parameters['variadicVar']['variadic']);
    }

    /**
     * @throws \ReflectionException
     * @throws ClassNotDeclaredException
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
}
