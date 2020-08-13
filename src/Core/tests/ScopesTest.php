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
use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\RuntimeException;
use Spiral\Tests\Core\Fixtures\Bucket;
use Spiral\Tests\Core\Fixtures\SampleClass;

class ScopesTest extends TestCase
{
    public function testScope(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $this->assertNull(ContainerScope::getContainer());

        $this->assertTrue(ContainerScope::runScope($container, function () use ($container) {
            return $container === ContainerScope::getContainer();
        }));

        $this->assertNull(ContainerScope::getContainer());
    }

    public function testScopeException(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $this->assertNull(ContainerScope::getContainer());

        try {
            $this->assertTrue(ContainerScope::runScope($container, function () use ($container): void {
                throw new RuntimeException('exception');
            }));
        } catch (\Throwable $e) {
        }

        $this->assertInstanceOf(RuntimeException::class, $e);
        $this->assertNull(ContainerScope::getContainer());
    }

    public function testContainerScope(): void
    {
        $c = new Container();
        $c->bind('bucket', new Bucket('a'));

        $this->assertSame('a', $c->get('bucket')->getName());
        $this->assertFalse($c->has('other'));

        $this->assertTrue($c->runScope([
            'bucket' => new Bucket('b'),
            'other'  => new SampleClass()
        ], function () use ($c) {
            $this->assertSame('b', $c->get('bucket')->getName());
            $this->assertTrue($c->has('other'));

            return $c->get('bucket')->getName() == 'b' && $c->has('other');
        }));

        $this->assertSame('a', $c->get('bucket')->getName());
        $this->assertFalse($c->has('other'));
    }

    public function testContainerScopeException(): void
    {
        $c = new Container();
        $c->bind('bucket', new Bucket('a'));

        $this->assertSame('a', $c->get('bucket')->getName());
        $this->assertFalse($c->has('other'));

        $this->assertTrue($c->runScope([
            'bucket' => new Bucket('b'),
            'other'  => new SampleClass()
        ], function () use ($c) {
            $this->assertSame('b', $c->get('bucket')->getName());
            $this->assertTrue($c->has('other'));

            return $c->get('bucket')->getName() == 'b' && $c->has('other');
        }));

        try {
            $this->assertTrue($c->runScope([
                'bucket' => new Bucket('b'),
                'other'  => new SampleClass()
            ], function () use ($c): void {
                throw new RuntimeException('exception');
            }));
        } catch (\Throwable $e) {
        }

        $this->assertSame('a', $c->get('bucket')->getName());
        $this->assertFalse($c->has('other'));
    }
}
