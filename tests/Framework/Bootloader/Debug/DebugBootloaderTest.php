<?php

declare(strict_types=1);

namespace Framework\Bootloader\Debug;

use Spiral\Boot\Environment\AppEnvironment;
use Spiral\Bootloader\DebugBootloader;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Debug\Config\DebugConfig;
use Spiral\Debug\Exception\StateException;
use Spiral\Debug\State;
use Spiral\Debug\StateCollector\EnvironmentCollector;
use Spiral\Debug\StateCollector\HttpCollector;
use Spiral\Debug\StateCollector\LogCollector;
use Spiral\Debug\StateCollectorInterface;
use Spiral\Debug\StateInterface;
use Spiral\Testing\Attribute\Config;
use Spiral\Tests\Framework\BaseTestCase;

final class DebugBootloaderTest extends BaseTestCase
{
    public function testStateInterfaceBinding(): void
    {
        $this->assertContainerBound(StateInterface::class, State::class);
    }

    public function testAddCollector(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(DebugConfig::CONFIG, ['collectors' => []]);

        $collector = $this->createMock(StateCollectorInterface::class);
        $autowire = new Autowire('foo');

        $bootloader = new DebugBootloader($this->getContainer(), $configs);
        $bootloader->addStateCollector('foo');
        $bootloader->addStateCollector($collector);
        $bootloader->addStateCollector($autowire);

        self::assertSame(['foo', $collector, $autowire], $configs->getConfig(DebugConfig::CONFIG)['collectors']);
    }

    public function testAddTag(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(DebugConfig::CONFIG, ['tags' => []]);

        $class = new class {
            public function __toString(): string
            {
                return 'value 2';
            }
        };
        $fn = static fn(mixed $a): string => 'value 3';

        $bootloader = new DebugBootloader($this->getContainer(), $configs);
        $bootloader->addTag('foo', 'value 1');
        $bootloader->addTag('bar', $class);
        $bootloader->addTag('baz', $fn);

        self::assertSame(['foo' => 'value 1', 'bar' => $class, 'baz' => $fn], $configs->getConfig(DebugConfig::CONFIG)['tags']);
    }

    #[Config('debug.tags', ['foo' => 'bar', 'baz' => 'qux'])]
    public function testGetTagsFromConfig(): void
    {
        self::assertSame(['foo' => 'bar', 'baz' => 'qux', 'php' => \phpversion()], $this->getContainer()->get(StateInterface::class)->getTags());
    }

    #[Config('debug.tags', ['foo' => new HttpCollector()])]
    public function testInvalidTagFromConfig(): void
    {
        try {
            $this->getContainer()->get(StateInterface::class)->getTags();
        } catch (NotFoundException $e) {
            self::assertInstanceOf(StateException::class, $e->getPrevious());
        }
    }

    #[Config('debug.collectors', ['foo'])]
    public function testGetCollectorsFromConfig(): void
    {
        $collector = $this->createMock(StateCollectorInterface::class);
        $collector->expects($this->once())->method('populate');

        $this->getContainer()->bindSingleton('foo', $collector);

        $this->getContainer()->get(StateInterface::class);
    }

    public function testResolveTagCallableDeps(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(DebugConfig::CONFIG, ['tags' => []]);

        $bootloader = new DebugBootloader($this->getContainer(), $configs);
        $ref = new \ReflectionMethod($bootloader, 'state');
        /** @see DebugBootloader::state() */
        $state = $ref->invoke($bootloader, $this->getContainer(), new DebugConfig([
            'tags' => [
                'env' => static fn(AppEnvironment $env): string => $env->isProduction() ? 'prod' : 'dev',
            ],
        ]));

        self::assertSame('dev', $state->getTags()['env']);
    }

    public function testDefaultConfig(): void
    {
        $config = $this->getConfig(DebugConfig::CONFIG);

        self::assertEquals([
            'collectors' => [
                EnvironmentCollector::class,
                new LogCollector(),
                new HttpCollector(),
            ],
            'tags' => [],
        ], $config);
    }
}
