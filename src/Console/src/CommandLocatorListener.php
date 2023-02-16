<?php

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Console\Traits\LazyTrait;
use Spiral\Tokenizer\Attribute\TargetClass;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Spiral\Tokenizer\Traits\TargetTrait;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

#[TargetClass(SymfonyCommand::class)]
final class CommandLocatorListener implements TokenizationListenerInterface
{
    use LazyTrait;
    use TargetTrait;

    /** @var \ReflectionClass[] */
    private array $commands = [];
    private readonly \ReflectionClass $target;

    public function __construct(
        private readonly ConsoleBootloader $bootloader,
        ContainerInterface $container
    ) {
        $this->container = $container;
        $this->target = new \ReflectionClass(SymfonyCommand::class);
    }

    public function listen(\ReflectionClass $class): void
    {
        if (!$this->isTargeted($class, $this->target)) {
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

            $this->bootloader->addCommand($class->getName());
        }
    }

    /**
     * Check if given class targeted by locator.
     */
    protected function isTargeted(\ReflectionClass $class, \ReflectionClass $target): bool
    {
        if (!$target->isTrait()) {
            // Target is interface or class
            return $class->isSubclassOf($target) || $class->getName() === $target->getName();
        }

        // Checking using traits
        return \in_array($target->getName(), $this->fetchTraits($class->getName()), true);
    }
}
