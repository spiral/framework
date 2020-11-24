<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\ArgumentException;
use Spiral\Core\Exception\Container\AutowireException;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\DependencyException;
use Spiral\Core\Exception\LogicException;
use Spiral\Tests\Core\Fixtures\UnionTypes;

class ExceptionsTest extends TestCase
{
    public function testInvalidBinding(): void
    {
        $this->expectExceptionMessage("Invalid binding for 'invalid'");
        $this->expectException(ContainerException::class);
        $container = new Container();
        $container->bind('invalid', ['invalid']);
        $container->get('invalid');
    }

    public function testClone(): void
    {
        $this->expectException(LogicException::class);
        $container = new Container();
        clone $container;
    }

    public function testInvalidInjectionParameter(): void
    {
        $expectExceptionMessage = \version_compare(\PHP_VERSION, '8.0') < 0
            ? 'Class Spiral\Tests\Core\InvalidClass does not exist'
            : 'Class "Spiral\Tests\Core\InvalidClass" does not exist';

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage($expectExceptionMessage);

        $container = new Container();

        $container->resolveArguments(new \ReflectionMethod($this, 'invalidInjection'));
    }

    /**
     * @requires PHP >= 8.0
     */
    public function testInjectionUsingUnionTypes(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('union type hint that cannot be inferred unambiguously');

        $container = new Container();

        $container->resolveArguments(new \ReflectionMethod(UnionTypes::class, 'example'));
    }

    public function testArgumentException(string $param = null): void
    {
        $method = new \ReflectionMethod($this, 'testArgumentException');

        $e = new ArgumentException(
            $method->getParameters()[0],
            $method
        );

        $this->assertInstanceOf(AutowireException::class, $e);
        $this->assertInstanceOf(ContainerException::class, $e);
        $this->assertInstanceOf(DependencyException::class, $e);
        $this->assertInstanceOf(ContainerExceptionInterface::class, $e);

        $this->assertSame($method, $e->getContext());
        $this->assertSame('param', $e->getParameter()->getName());
    }

    protected function invalidInjection(InvalidClass $class): void
    {
    }
}
