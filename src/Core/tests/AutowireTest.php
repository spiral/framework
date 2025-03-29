<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Core\Exception\Resolver\ArgumentResolvingException;
use Spiral\Tests\Core\Fixtures\Bucket;
use Spiral\Tests\Core\Fixtures\DependedClass;
use Spiral\Tests\Core\Fixtures\ExtendedSample;
use Spiral\Tests\Core\Fixtures\SampleClass;
use Spiral\Tests\Core\Fixtures\SoftDependedClass;
use Spiral\Tests\Core\Fixtures\TypedClass;
use Spiral\Tests\Core\Fixtures\UnionTypes;

/**
 * The most fun test.
 */
class AutowireTest extends TestCase
{
    public function testSimple(): void
    {
        $container = new Container();

        self::assertInstanceOf(SampleClass::class, $container->get(SampleClass::class));
        self::assertInstanceOf(SampleClass::class, $container->make(SampleClass::class, []));
    }

    public function testGet(): void
    {
        $container = new Container();

        $container->bind(SampleClass::class, ExtendedSample::class);
        self::assertInstanceOf(ExtendedSample::class, $container->get(SampleClass::class));
    }

    public function testMake(): void
    {
        $container = new Container();

        $container->bind(SampleClass::class, ExtendedSample::class);
        self::assertInstanceOf(ExtendedSample::class, $container->make(SampleClass::class, []));
    }

    public function testMakeFromClassNameBinding(): void
    {
        $container = new Container();

        $container->bind(SampleClass::class, SampleClass::class);
        self::assertInstanceOf(SampleClass::class, $container->make(SampleClass::class, []));
    }

    public function testArgumentException(): void
    {
        $expected = 'Unable to resolve required argument `name` when resolving';
        $this->expectExceptionMessage($expected);
        $this->expectException(ArgumentResolvingException::class);

        $container = new Container();
        $container->get(Bucket::class);
    }

    public function testDefaultValue(): void
    {
        $container = new Container();

        $bucket = $container->make(Bucket::class, ['name' => 'abc']);

        self::assertInstanceOf(Bucket::class, $bucket);
        self::assertSame('abc', $bucket->getName());
        self::assertSame('default-data', $bucket->getData());
    }

    public function testCascade(): void
    {
        $container = new Container();

        $object = $container->make(
            DependedClass::class,
            [
                'name' => 'some-name',
            ],
        );

        self::assertInstanceOf(DependedClass::class, $object);
        self::assertSame('some-name', $object->getName());
        self::assertInstanceOf(SampleClass::class, $object->getSample());
    }

    public function testRemoveBinding(): void
    {
        $container = new Container();

        $container->bind('alias', $this);

        self::assertTrue($container->has('alias'));
        self::assertTrue($container->hasInstance('alias'));

        $container->removeBinding('alias');

        self::assertFalse($container->has('alias'));
        self::assertFalse($container->hasInstance('alias'));

        $container->bind('alias-b', 'alias');
        self::assertFalse($container->hasInstance('alias-b'));
    }

    public function testCascadeFollowBindings(): void
    {
        $container = new Container();

        $container->bind(SampleClass::class, ExtendedSample::class);

        $object = $container->make(
            DependedClass::class,
            [
                'name' => 'some-name',
            ],
        );

        self::assertInstanceOf(DependedClass::class, $object);
        self::assertSame('some-name', $object->getName());
        self::assertInstanceOf(ExtendedSample::class, $object->getSample());
    }

    public function testAutowireException(): void
    {
        $this->expectExceptionMessage(
            'Can\'t resolve `Spiral\Tests\Core\Fixtures\DependedClass`.',
        );
        $this->expectExceptionMessage(
            'Can\'t autowire `WrongClass`: class or injector not found',
        );
        $this->expectException(NotFoundException::class);
        $container = new Container();

        $container->bind(SampleClass::class, \WrongClass::class);
        $container->make(
            DependedClass::class,
            [
                'name' => 'some-name',
            ],
        );
    }

    /**
     * See line 218 in Container, this behaviour allows system to pass on classes which can not be
     * automatically constructured or missing but ONLY when default value is set to NULL.
     */
    public function testAutowireWithDefaultOnWrongClass(): void
    {
        $container = new Container();

        /** @psalm-suppress UndefinedClass */
        $container->bind(SampleClass::class, \WrongClass::class);

        $object = $container->make(
            SoftDependedClass::class,
            [
                'name' => 'some-name',
            ],
        );

        self::assertInstanceOf(SoftDependedClass::class, $object);
        self::assertSame('some-name', $object->getName());
        self::assertNull($object->getSample());
    }

    public function testAutowireTypecastingAndValidatingWrongString(): void
    {
        $this->expectValidationException('string');

        $container = new Container();

        $container->make(
            TypedClass::class,
            [
                'string' => null,
                'int'    => 123,
                'float'  => 123.00,
                'bool'   => true,
            ],
        );
    }

    public function testCallMethodWithNullValueOnNullableScalar(): void
    {
        $container = new Container();

        $result = $container->invoke(
            [SampleClass::class, 'nullableScalar'],
            [
                'nullable' => null,
            ],
        );

        self::assertNull($result);
    }

    public function testCallMethodWithNullValueOnScalarUnionNull(): void
    {
        $container = new Container();

        $result = $container->invoke(
            UnionTypes::unionNull(...),
            [
                'nullable' => null,
            ],
        );

        self::assertNull($result);
    }

    public function testAutowireTypecastingAndValidatingWrongInt(): void
    {
        $this->expectValidationException('int');

        $container = new Container();

        $container->make(
            TypedClass::class,
            [
                'string' => '',
                'int'    => 'yo!',
                'float'  => 123.00,
                'bool'   => true,
            ],
        );
    }

    public function testAutowireTypecastingAndValidatingWrongFloat(): void
    {
        $this->expectValidationException('float');

        $container = new Container();

        $container->make(
            TypedClass::class,
            [
                'string' => '',
                'int'    => 123,
                'float'  => '~',
                'bool'   => true,
            ],
        );
    }

    public function testAutowireTypecastingAndValidatingWrongBool(): void
    {
        $this->expectValidationException('bool');

        $container = new Container();

        $container->make(
            TypedClass::class,
            [
                'string' => '',
                'int'    => 123,
                'float'  => 1.00,
                'bool'   => 'true',
            ],
        );
    }

    public function testAutowireTypecastingAndValidatingWrongArray(): void
    {
        $this->expectValidationException('array');

        $container = new Container();

        $container->make(
            TypedClass::class,
            [
                'string' => '',
                'int'    => 123,
                'float'  => 1.00,
                'bool'   => true,
                'array'  => 'not array',
            ],
        );
    }

    public function testAutowireOptionalArray(): void
    {
        $container = new Container();

        $object = $container->make(
            TypedClass::class,
            [
                'string' => '',
                'int'    => 123,
                'float'  => 1.00,
                'bool'   => true,
            ],
        );

        self::assertInstanceOf(TypedClass::class, $object);
    }

    public function testAutowireOptionalString(): void
    {
        $container = new Container();

        $object = $container->make(
            TypedClass::class,
            [
                'string' => '',
                'int'    => 123,
                'float'  => 1.00,
                'bool'   => true,
                'pong'   => null,
            ],
        );

        self::assertInstanceOf(TypedClass::class, $object);
    }

    public function testAutowireDelegate(): void
    {
        $container = new Container();

        $container->bind('sample-binding', $s = new SampleClass());

        $object = $container->make(
            SoftDependedClass::class,
            [
                'name'   => 'some-name',
                'sample' => new Container\Autowire('sample-binding'),
            ],
        );

        self::assertSame($s, $object->getSample());
    }

    public function testSerializeAutowire(): void
    {
        $wire = new Container\Autowire('sample-binding', ['a' => new Container\Autowire('b')]);

        $wireb = \unserialize(\serialize($wire));

        self::assertEquals($wire, $wireb);
    }

    public function testBingToAutowire(): void
    {
        $container = new Container();
        $container->bind(
            'abc',
            new Container\Autowire(
                SoftDependedClass::class,
                [
                    'name' => 'Fixed',
                ],
            ),
        );

        /**
         * @var SoftDependedClass $abc
         */
        $abc = $container->get('abc');

        self::assertSame('Fixed', $abc->getName());
    }

    public function testGetAutowire(): void
    {
        $container = new Container();

        /**
         * @var SoftDependedClass $abc
         */
        $abc = $container->get(
            new Container\Autowire(
                SoftDependedClass::class,
                [
                    'name' => 'Fixed',
                ],
            ),
        );

        self::assertSame('Fixed', $abc->getName());
    }

    public function testBingToAutowireWithParameters(): void
    {
        $container = new Container();
        $container->bind(
            'abc',
            new Container\Autowire(
                SoftDependedClass::class,
                [
                    'name' => 'Fixed',
                ],
            ),
        );

        /**
         * @var SoftDependedClass $abc
         */
        $abc = $container->make('abc', ['name' => 'Overwritten']);

        self::assertSame('Overwritten', $abc->getName());
    }

    public function testBingToAutowireWithParametersViaArray(): void
    {
        $container = new Container();
        $container->bind(
            'abc',
            Container\Autowire::wire(
                [
                    'class'   => SoftDependedClass::class,
                    'options' => [
                        'name' => 'Fixed',
                    ],
                ],
            ),
        );

        /**
         * @var SoftDependedClass $abc
         */
        $abc = $container->make('abc', ['name' => 'Overwritten']);

        self::assertSame('Overwritten', $abc->getName());
    }

    public function testSerialize(): void
    {
        $a = new Container\Autowire(
            SoftDependedClass::class,
            [
                'name' => 'Fixed',
            ],
        );

        $b = Container\Autowire::__set_state(
            [
                'alias'      => SoftDependedClass::class,
                'parameters' => ['name' => 'Fixed'],
            ],
        );
        self::assertEquals($a, $b);
    }

    private function expectValidationException(string $parameter): void
    {
        // $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            "Invalid argument value type for the `$parameter` parameter when validating arguments",
        );
    }
}
