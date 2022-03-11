<?php

/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @author    Vladislav Gorenkin (vladgorenkin)
 */

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Console\CommandLocator;
use Spiral\Console\StaticLocator;
use Spiral\Tests\Console\Fixtures\LazyLoadedCommand;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ScopedClassesInterface;
use Symfony\Component\Console\Command\LazyCommand;

class LazyTest extends BaseTest
{
    public function testLazyCommandCreationInCommandLocator(): void
    {
        $locator = new CommandLocator(
            new class() implements ScopedClassesInterface {
                public function getScopedClasses(string $scope, $target = null): array
                {
                    return [
                        new \ReflectionClass(LazyLoadedCommand::class),
                    ];
                }
            },
            $this->container
        );
        $commands = $locator->locateCommands();
        $command = reset($commands);

        $this->assertInstanceOf(LazyCommand::class, $command);
        $this->assertSame('lazy', $command->getName());
        $this->assertSame('Lazy description', $command->getDescription());
    }

    public function testLazyCommandCreationInStaticLocator(): void
    {
        $locator = new StaticLocator([LazyLoadedCommand::class]);
        $commands = $locator->locateCommands();
        $command = reset($commands);

        $this->assertInstanceOf(LazyCommand::class, $command);
        $this->assertSame('lazy', $command->getName());
        $this->assertSame('Lazy description', $command->getDescription());
    }

    public function testLazyCommandExecution(): void
    {
        $core = $this->getCore(new StaticLocator([LazyLoadedCommand::class]));
        $output = $core->run('lazy');
        $this->assertSame('OK', $output->getOutput()->fetch());
    }
}
