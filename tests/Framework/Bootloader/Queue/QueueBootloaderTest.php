<?php

declare(strict_types=1);

namespace Framework\Bootloader\Queue;

use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Queue\Bootloader\QueueBootloader;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\Failed\FailedJobHandlerInterface;
use Spiral\Queue\Failed\LogFailedJobHandler;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\Interceptor\Consume\ErrorHandlerInterceptor;
use Spiral\Queue\Interceptor\Consume\Handler;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\Queue\QueueInterface;
use Spiral\Queue\QueueManager;
use Spiral\Queue\QueueRegistry;
use Spiral\Tests\Framework\BaseTest;

final class QueueBootloaderTest extends BaseTest
{
    public const ENV = [
        'QUEUE_CONNECTION' => 'foo',
    ];

    public function testHandlerRegistryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(HandlerRegistryInterface::class, QueueRegistry::class);
    }

    public function testFailedJobHandlerInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(FailedJobHandlerInterface::class, LogFailedJobHandler::class);
    }

    public function testQueueConnectionProviderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(QueueConnectionProviderInterface::class, QueueManager::class);
    }

    public function testQueueManagerBinding(): void
    {
        $this->assertContainerBoundAsSingleton(QueueManager::class, QueueManager::class);
    }

    public function testQueueRegistryBinding(): void
    {
        $this->assertContainerBoundAsSingleton(QueueRegistry::class, QueueRegistry::class);
    }

    public function testHandlerBinding(): void
    {
        $this->assertContainerBoundAsSingleton(Handler::class, Handler::class);
    }

    public function testQueueBinding(): void
    {
        $mock = $this->mockContainer(QueueConnectionProviderInterface::class);
        $mock->shouldReceive('getConnection')->andReturn($queue = $this->createMock(QueueInterface::class));

        $this->assertSame($queue, $this->getContainer()->get(QueueInterface::class));
    }

    public function testConfig(): void
    {
        $this->assertConfigMatches(QueueConfig::CONFIG, [
            'default' => 'foo',
            'connections' => [
                'sync' => ['driver' => 'sync'],
            ],

            'registry' => [
                'handlers' => [],
                'serializers' => [],
            ],
            'driverAliases' => [
                'sync' => \Spiral\Queue\Driver\SyncDriver::class,
                'null' => \Spiral\Queue\Driver\NullDriver::class,
            ],
            'interceptors' => [
                'consume' => [
                    \Spiral\Queue\Interceptor\Consume\ErrorHandlerInterceptor::class,
                ],
                'push' => []
            ],
        ]);
    }

    public function testAddConsumeInterceptor(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(QueueConfig::CONFIG, ['interceptors' => ['consume' => []]]);

        $interceptor = new ErrorHandlerInterceptor($this->createMock(FailedJobHandlerInterface::class));
        $autowire = new Autowire(ErrorHandlerInterceptor::class);

        $bootloader = new QueueBootloader($configs);
        $bootloader->addConsumeInterceptor('foo');
        $bootloader->addConsumeInterceptor($interceptor);
        $bootloader->addConsumeInterceptor($autowire);

        $this->assertSame([
            'foo', $interceptor, $autowire
        ], $configs->getConfig(QueueConfig::CONFIG)['interceptors']['consume']);
    }

    public function testAddPushInterceptor(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(QueueConfig::CONFIG, ['interceptors' => ['push' => []]]);

        $interceptor = new ErrorHandlerInterceptor($this->createMock(FailedJobHandlerInterface::class));
        $autowire = new Autowire(ErrorHandlerInterceptor::class);

        $bootloader = new QueueBootloader($configs);
        $bootloader->addPushInterceptor('foo');
        $bootloader->addPushInterceptor($interceptor);
        $bootloader->addPushInterceptor($autowire);

        $this->assertSame([
            'foo', $interceptor, $autowire
        ], $configs->getConfig(QueueConfig::CONFIG)['interceptors']['push']);
    }

    public function testRegisterDriverAlias(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(QueueConfig::CONFIG, ['driverAliases' => []]);

        $bootloader = new QueueBootloader($configs);
        $bootloader->registerDriverAlias('foo', 'bar');

        $this->assertSame([
            'bar' => 'foo'
        ], $configs->getConfig(QueueConfig::CONFIG)['driverAliases']);
    }
}
