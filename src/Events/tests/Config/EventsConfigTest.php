<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Config;

use PHPUnit\Framework\TestCase;
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
}
