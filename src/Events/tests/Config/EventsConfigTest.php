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

        $this->assertSame([], $config->getProcessors());
    }

    public function testGetsProcessors(): void
    {
        $config = new EventsConfig([
            'processors' => ['foo', 'bar']
        ]);

        $this->assertSame(['foo', 'bar'], $config->getProcessors());
    }

    public function testGetsEmptyListeners(): void
    {
        $config = new EventsConfig();

        $this->assertSame([], $config->getListeners());
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

        $this->assertSame($listener, $config->getListeners()['foo'][1]);
        $this->assertInstanceOf(EventListener::class, $config->getListeners()['foo'][0]);
        $this->assertSame('bar', $config->getListeners()['foo'][0]->listener);
    }

    public function testGetsEmptyInterceptors(): void
    {
        $config = new EventsConfig();

        $this->assertSame([], $config->getInterceptors());
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

        $this->assertSame('bar', $config->getInterceptors()[0]);
        $this->assertInstanceOf(CoreInterceptorInterface::class, $config->getInterceptors()[1]);
        $this->assertInstanceOf(Autowire::class, $config->getInterceptors()[2]);
    }
}
