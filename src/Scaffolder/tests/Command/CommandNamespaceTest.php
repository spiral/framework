<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use Mockery as m;
use Spiral\Console\Command;
use Spiral\Core\FactoryInterface;
use Spiral\Scaffolder\Declaration\CommandDeclaration;
use Spiral\Scaffolder\Declaration\DeclarationInterface;

final class CommandNamespaceTest extends AbstractCommandTestCase
{
    public function testCommandWithoutNamespaceOption(): void
    {
        $this->app->getContainer()->bind(
            FactoryInterface::class,
            $factory = m::mock(FactoryInterface::class),
        );

        $factory->shouldReceive('make')
            ->once()
            ->with(CommandDeclaration::class, [
                'name' => 'foo',
                'comment' => null,
                'namespace' => null,
            ])
            ->andReturn(m::mock(DeclarationInterface::class));

        $output = $this->console()->run('create:command-without-namespace', [
            'name' => 'foo',
        ]);

        self::assertSame(Command::SUCCESS, $output->getCode());
    }

    public function testCommandWithNamespaceOption(): void
    {
        $this->app->getContainer()->bind(
            FactoryInterface::class,
            $factory = m::mock(FactoryInterface::class),
        );

        $factory->shouldReceive('make')
            ->once()
            ->with(CommandDeclaration::class, [
                'name' => 'foo',
                'comment' => null,
                'namespace' => 'App\Command',
            ])
            ->andReturn(m::mock(DeclarationInterface::class));

        $output = $this->console()->run('create:command-with-namespace', [
            'name' => 'foo',
            '--namespace' => 'App\Command'
        ]);

        self::assertSame(Command::SUCCESS, $output->getCode());
    }

    public function testCommandWithCommentOption(): void
    {
        $this->app->getContainer()->bind(
            FactoryInterface::class,
            $factory = m::mock(FactoryInterface::class),
        );

        $factory->shouldReceive('make')
            ->once()
            ->with(CommandDeclaration::class, [
                'name' => 'foo',
                'comment' => 'Some command',
                'namespace' => null,
            ])
            ->andReturn(m::mock(DeclarationInterface::class));

        $output = $this->console()->run('create:command-with-namespace', [
            'name' => 'foo',
            '--comment' => 'Some command'
        ]);

        self::assertSame(Command::SUCCESS, $output->getCode());
    }
}
