<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Mockery as m;
use Psr\Container\ContainerInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Queue\ContainerRegistry;
use Spiral\Queue\Exception\JobException;
use Spiral\Queue\HandlerInterface;

final class ContainerRegistryT extends TestCase
{
    private ContainerInterface|m\MockInterface $container;
    private ContainerRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = m::mock(ContainerInterface::class);
        $this->registry = new ContainerRegistry($this->container);
    }

    public function testGetsHandlerByJobType(): void
    {
        $this->container->shouldReceive('get')
            ->with('Mail\Job')
            ->andReturn($handler = m::mock(HandlerInterface::class));

        $this->registry->getHandler('mail.job');

        self::assertSame($handler, $this->registry->getHandler('mail.job'));
    }

    public function testGetsHandlerWithWrongInterface(): void
    {
        $this->expectException(JobException::class);
        $this->expectExceptionMessage('Unable to resolve job handler for `mail.job`');

        $this->container->shouldReceive('get')
            ->with('Mail\Job')
            ->andReturn(m::mock('test'));

        $this->registry->getHandler('mail.job');
    }

    public function testGetsNotExistsHandler(): void
    {
        $this->expectException(JobException::class);
        $this->expectExceptionMessage("Undefined class or binding 'Mail\Job'");

        $this->container->shouldReceive('get')
            ->with('Mail\Job')
            ->andThrow(new ContainerException("Undefined class or binding 'Mail\Job'"));

        $this->registry->getHandler('mail.job');
    }
}
