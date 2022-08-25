<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Console\Traits\LazyTrait;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Spiral\Tokenizer\Traits\TargetTrait;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

final class CommandLocatorListener implements TokenizationListenerInterface
{
    use LazyTrait;
    use TargetTrait;

    /** @var \ReflectionClass[] */
    private array $commands = [];

    private \ReflectionClass $target;

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly ConsoleBootloader $bootloader,
        private readonly ContainerInterface $container
    ) {
        $this->target = new \ReflectionClass(SymfonyCommand::class);
    }

    public function listen(\ReflectionClass $class): void
    {
        if (! $this->isTargeted($class, $this->target)) {
            return;
        }

        $this->commands[] = $class;
    }

    public function finalize(): void
    {
        foreach ($this->commands as $class) {
            if ($class->isAbstract()) {
                continue;
            }

            $this->bootloader->addCommand(
                $this->supportsLazyLoading($class->getName())
                    ? $this->createLazyCommand($class->getName())
                    : $this->container->get($class->getName())
            );
        }
    }
}
