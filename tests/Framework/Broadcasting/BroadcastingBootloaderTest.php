<?php

declare(strict_types=1);

namespace Framework\Broadcasting;

use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Spiral\App\TestApp;
use Spiral\Broadcasting\BroadcastInterface;
use Spiral\Broadcasting\BroadcastManager;
use Spiral\Broadcasting\BroadcastManagerInterface;
use Spiral\Broadcasting\Driver\LogBroadcast;
use Spiral\Broadcasting\Driver\NullBroadcast;
use Spiral\Broadcasting\GuardInterface;
use Spiral\Tests\Framework\BaseTest;

final class BroadcastingBootloaderTest extends BaseTest
{
    private TestApp $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = $this->makeApp();

        $this->app->getContainer()
            ->bind(LoggerInterface::class, \Mockery::mock(LoggerInterface::class));
    }


    public function testBindings()
    {
        $this->assertInstanceOf(LogBroadcast::class, $this->app->get(BroadcastInterface::class));
        $this->assertInstanceOf(BroadcastManager::class, $this->app->get(BroadcastManagerInterface::class));
    }

    public function testGetsConnectionByAlias()
    {
        $manager = $this->app->get(BroadcastManagerInterface::class);
        $this->assertInstanceOf(NullBroadcast::class, $manager->connection('firebase'));
    }
}
