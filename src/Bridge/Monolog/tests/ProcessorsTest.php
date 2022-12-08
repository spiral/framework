<?php

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use Monolog\Processor\PsrLogMessageProcessor;
use Spiral\Boot\BootloadManager\StrategyBasedBootloadManager;
use Spiral\Boot\FinalizerInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\LoaderInterface;
use Spiral\Logger\ListenerRegistry;
use Spiral\Logger\ListenerRegistryInterface;
use Spiral\Logger\LogsInterface;
use Spiral\Monolog\Bootloader\MonologBootloader;
use Spiral\Monolog\Config\MonologConfig;
use Spiral\Monolog\Exception\ConfigException;

class ProcessorsTest extends BaseTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->container->bind(FinalizerInterface::class, $finalizer = \Mockery::mock(FinalizerInterface::class));
        $finalizer->shouldReceive('addFinalizer')->once();

        $this->container->bind(ConfiguratorInterface::class, new ConfigManager(
            new class() implements LoaderInterface {
                public function has(string $section): bool
                {
                    return false;
                }

                public function load(string $section): array
                {
                    return [];
                }
            }
        ));
        $this->container->bindSingleton(ListenerRegistryInterface::class, new ListenerRegistry());
        $this->container->get(StrategyBasedBootloadManager::class)->bootload([MonologBootloader::class]);
    }

    public function testDefaultProcessor(): void
    {
        $this->container->bind(MonologConfig::class, new MonologConfig());

        $logger = $this->container->get(LogsInterface::class)->getLogger('test');
        $this->assertSame('test', $logger->getName());
        $this->assertCount(1, $logger->getProcessors());
        $this->assertInstanceOf(PsrLogMessageProcessor::class, $logger->getProcessors()[0]);
    }

    public function testInvalidProcessor(): void
    {
        $this->expectException(ConfigException::class);

        $this->container->bind(MonologConfig::class, new MonologConfig([
            'processors' => [
                'test' => [
                    ['what?']
                ]
            ]
        ]));

        $this->container->get(LogsInterface::class)->getLogger('test');
    }

    public function testObjectProcessor(): void
    {
        $this->container->bind(MonologConfig::class, new MonologConfig([
            'processors' => [
                'test' => [
                    new PsrLogMessageProcessor()
                ]
            ]
        ]));

        $logger = $this->container->get(LogsInterface::class)->getLogger('test');;

        $this->assertCount(1, $logger->getProcessors());
        $this->assertInstanceOf(PsrLogMessageProcessor::class, $logger->getProcessors()[0]);
    }

    public function testBindedProcessor(): void
    {
        $this->container->bind('PsrLogMessageProcessor', new PsrLogMessageProcessor());
        $this->container->bind(MonologConfig::class, new MonologConfig([
            'processors' => [
                'test' => [
                    'PsrLogMessageProcessor'
                ]
            ]
        ]));

        $logger = $this->container->get(LogsInterface::class)->getLogger('test');

        $this->assertCount(1, $logger->getProcessors());
        $this->assertInstanceOf(PsrLogMessageProcessor::class, $logger->getProcessors()[0]);
        $this->assertSame($this->container->get('PsrLogMessageProcessor'), $logger->getProcessors()[0]);
    }

    public function testConstructProcessor(): void
    {
        $this->container->bind(MonologConfig::class, new MonologConfig([
            'processors' => [
                'test' => [
                    [
                        'class' => PsrLogMessageProcessor::class
                    ]
                ]
            ]
        ]));

        $logger = $this->container->get(LogsInterface::class)->getLogger('test');

        $this->assertCount(1, $logger->getProcessors());
        $this->assertInstanceOf(PsrLogMessageProcessor::class, $logger->getProcessors()[0]);
    }

    public function testConstructWithOptionsProcessor(): void
    {
        $this->container->bind(MonologConfig::class, new MonologConfig([
            'processors' => [
                'test' => [
                    [
                        'class'   => PsrLogMessageProcessor::class,
                        'options' => [
                            'dateFormat' => 'c'
                        ]
                    ]
                ]
            ]
        ]));

        $logger = $this->container->get(LogsInterface::class)->getLogger('test');
        $processor = $logger->getProcessors()[0];

        $this->assertCount(1, $logger->getProcessors());
        $this->assertInstanceOf(PsrLogMessageProcessor::class, $processor);

        $property = new \ReflectionProperty(PsrLogMessageProcessor::class, 'dateFormat');

        $this->assertSame('c', $property->getValue($processor));
    }
}
