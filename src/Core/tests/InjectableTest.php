<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Core;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Spiral\Core\ConfigsInterface;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\AutowireException;
use Spiral\Core\Exception\Container\InjectionException;
use Spiral\Tests\Core\Fixtures\InvalidInjector;
use Spiral\Tests\Core\Fixtures\SampleClass;
use Spiral\Tests\Core\Fixtures\TestConfig;

class InjectableTest extends TestCase
{
    public function testMissingInjector(): void
    {
        $this->expectExceptionMessage("Undefined class or binding 'Spiral\Core\ConfigsInterface'");
        $this->expectException(AutowireException::class);

        $container = new Container();
        $container->get(TestConfig::class);
    }

    public function testInvalidInjector(): void
    {
        $excepted = "Class 'Spiral\Tests\Core\Fixtures\InvalidInjector' must be an " .
                    "instance of InjectorInterface for 'Spiral\Tests\Core\Fixtures\TestConfig'";
        $this->expectException(InjectionException::class);
        $this->expectExceptionMessage($excepted);

        $container = new Container();

        $container->bindInjector(TestConfig::class, InvalidInjector::class);
        $container->get(TestConfig::class);
    }

    public function testInvalidInjectorBinding(): void
    {
        $this->expectException(AutowireException::class);
        $this->expectExceptionMessage("Undefined class or binding 'invalid-injector'");

        $container = new Container();

        $container->bindInjector(TestConfig::class, 'invalid-injector');
        $container->get(TestConfig::class);
    }

    public function testInvalidRuntimeInjector(): void
    {
        $excepted = "Class 'Spiral\Tests\Core\Fixtures\InvalidInjector' must be an " .
            "instance of InjectorInterface for 'Spiral\Tests\Core\Fixtures\TestConfig'";
        $this->expectException(InjectionException::class);
        $this->expectExceptionMessage($excepted);

        $container = new Container();

        $container->bindInjector(TestConfig::class, 'invalid-injector');
        $container->bind('invalid-injector', new InvalidInjector());

        $container->get(TestConfig::class);
    }

    public function testGetInjectors(): void
    {
        $container = new Container();

        $container->bindInjector(TestConfig::class, 'invalid-injector');

        $injectors = $container->getInjectors();

        $this->assertNotEmpty($injectors);
        $this->assertArrayHasKey(TestConfig::class, $injectors);
        $this->assertSame('invalid-injector', $injectors[TestConfig::class]);

        $container->removeInjector(TestConfig::class);
        $injectors = $container->getInjectors();

        $this->assertEmpty($injectors);
    }

    public function testInjectorOuterBinding(): void
    {
        $this->expectException(AutowireException::class);
        $this->expectExceptionMessage("Undefined class or binding 'invalid-configurator'");
        $container = new Container();
        $container->bind(ConfigsInterface::class, 'invalid-configurator');

        $container->get(TestConfig::class);
    }

    public function testInvalidInjection(): void
    {
        $this->expectException(InjectionException::class);
        $this->expectExceptionMessage("Invalid injection response for 'Spiral\Tests\Core\Fixtures\TestConfig'");

        $container = new Container();

        $configurator = m::mock(ConfigsInterface::class);
        $container->bind(ConfigsInterface::class, $configurator);

        $configurator->shouldReceive('createInjection')->andReturn(new SampleClass());

        $container->get(TestConfig::class);
    }

    public function testInjector(): void
    {
        $configurator = m::mock(ConfigsInterface::class);
        $expected = new TestConfig();

        $container = new Container();
        $container->bind(ConfigsInterface::class, $configurator);

        $configurator->shouldReceive('createInjection')
            ->with(m::on(static function (ReflectionClass $r) {
                return $r->getName() === TestConfig::class;
            }), null)
            ->andReturn($expected)
        ;

        $this->assertSame($expected, $container->get(TestConfig::class));
    }

    public function testInjectorWithContext(): void
    {
        $configurator = m::mock(ConfigsInterface::class);
        $expected = new TestConfig();

        $container = new Container();
        $container->bind(ConfigsInterface::class, $configurator);

        $configurator->shouldReceive('createInjection')
            ->with(m::on(static function (ReflectionClass $r) {
                return $r->getName() === TestConfig::class;
            }), 'context')
            ->andReturn($expected)
        ;

        $this->assertSame($expected, $container->get(TestConfig::class, 'context'));
    }

    public function testInjectorForMethod(): void
    {
        $configurator = m::mock(ConfigsInterface::class);
        $expected = new TestConfig();

        $container = new Container();
        $container->bind(ConfigsInterface::class, $configurator);

        $configurator->shouldReceive('createInjection')
            ->with(m::on(static function (ReflectionClass $r) {
                return $r->getName() === TestConfig::class;
            }), 'contextArgument')
            ->andReturn($expected)
        ;

        $arguments = $container->resolveArguments(new ReflectionMethod($this, 'methodInjection'));

        $this->assertCount(1, $arguments);
        $this->assertSame($expected, $arguments[0]);
    }

    /**
     * @param TestConfig $contextArgument
     */
    private function methodInjection(TestConfig $contextArgument): void
    {
    }
}
