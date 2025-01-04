<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Config;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container\Autowire;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Events\Config\EventListener;
use Spiral\Events\Config\EventsConfig;

final class EventsConfigTest extends TestCase
{
    public function testGetsEmptyProcessors(): void
    {
        $config = new EventsConfig();

        self::assertSame([], $config->getProcessors());
    }

    public function testGetsProcessors(): void
    {
        $config = new EventsConfig([
            'processors' => ['foo', 'bar']
        ]);

        self::assertSame(['foo', 'bar'], $config->getProcessors());
    }

    public function testGetsEmptyListeners(): void
    {
        $config = new EventsConfig();

        self::assertSame([], $config->getListeners());
    }

    public function testGetsListeners(): void
    {
        $config = new EventsConfig([
            'listeners' => [
                'foo' => [
                    'bar',
                    $listener = new EventListener('baz')
                ]
            ]
        ]);

        self::assertSame($listener, $config->getListeners()['foo'][1]);
        self::assertInstanceOf(EventListener::class, $config->getListeners()['foo'][0]);
        self::assertSame('bar', $config->getListeners()['foo'][0]->listener);
    }

    public function testGetsEmptyInterceptors(): void
    {
        $config = new EventsConfig();

        self::assertSame([], $config->getInterceptors());
    }

    public function testGetsInterceptors(): void
    {
        $config = new EventsConfig([
            'interceptors' => [
                'bar',
                new class implements CoreInterceptorInterface {
                    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
                    {
                    }
                },
                new Autowire('foo')
            ]
        ]);

        self::assertSame('bar', $config->getInterceptors()[0]);
        self::assertInstanceOf(CoreInterceptorInterface::class, $config->getInterceptors()[1]);
        self::assertInstanceOf(Autowire::class, $config->getInterceptors()[2]);
    }
}
