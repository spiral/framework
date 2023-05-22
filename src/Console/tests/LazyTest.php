<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Tests\Console\Fixtures\LazyLoadedCommand;
use Spiral\Tokenizer\ScopedClassesInterface;
use Symfony\Component\Console\Command\LazyCommand;

class LazyTest extends BaseTestCase
{
    public function testLazyCommandCreationInCommandLocator(): void
    {
        $locator = $this->getCommandLocator(
            new class() implements ScopedClassesInterface {
                public function getScopedClasses(string $scope, $target = null): array
                {
                    return [
                        new \ReflectionClass(LazyLoadedCommand::class),
                    ];
                }
            }
        );
        $commands = $locator->locateCommands();
        $command = reset($commands);

        $this->assertInstanceOf(LazyCommand::class, $command);
        $this->assertSame('lazy', $command->getName());
        $this->assertSame('Lazy description', $command->getDescription());
    }

    public function testLazyCommandCreationInStaticLocator(): void
    {
        $locator = $this->getStaticLocator([LazyLoadedCommand::class]);
        $commands = $locator->locateCommands();
        $command = reset($commands);

        $this->assertInstanceOf(LazyCommand::class, $command);
        $this->assertSame('lazy', $command->getName());
        $this->assertSame('Lazy description', $command->getDescription());
    }

    public function testLazyCommandExecution(): void
    {
        $core = $this->getCore($this->getStaticLocator([LazyLoadedCommand::class]));
        $output = $core->run('lazy');
        $this->assertSame('OK', $output->getOutput()->fetch());
    }
}
