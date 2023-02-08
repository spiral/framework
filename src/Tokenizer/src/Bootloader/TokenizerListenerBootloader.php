<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Bootloader;

use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Tokenizer\Attribute\ListenAttribute;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

// Current
// 1. $classes = ClassLocator::getClasses()
// 2. Iterate over $classes and pass each class to TokenizationListenerInterface::listen
// 3. Iterate over TokenizationListenerInterface::finalize

// New way
// 1. Iterate over TokenizationListenerInterface method attributes and collect criteria
// 2. Find cache for criteria
// 3. If cache found, iterate over $classes and pass each class to TokenizationListenerInterface::listen and then once TokenizationListenerInterface::finalize and exclude listener from list
// 4. $classes = ClassLocator::getClasses() for listeners without cache
// 5. Iterate over $classes and pass each class to TokenizationListenerInterface::listen
// 6. Iterate over TokenizationListenerInterface::finalize

// Console command app:cache:warmup
// Console command app:cache:clear


final class TokenizerListenerBootloader extends Bootloader implements
    SingletonInterface,
    TokenizerListenerRegistryInterface
{
    protected const DEPENDENCIES = [
        TokenizerBootloader::class,
    ];

    protected const SINGLETONS = [
        TokenizerListenerRegistryInterface::class => self::class,
    ];

    /** @var TokenizationListenerInterface[] */
    private array $listeners = [];

    public function addListener(TokenizationListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function boot(AbstractKernel $kernel): void
    {
        $kernel->booted(function (ClassesInterface $classes, ReaderInterface $reader): void {
            $classes = $classes->getClasses();

            foreach ($this->listeners as $listener) {
                $listener = new \ReflectionClass($listener);

                if ($attribute = $reader->firstClassMetadata($listener, ListenAttribute::class)) {
                    $ref = new \ReflectionClass($attribute);
                }
            }

            foreach ($classes->getClasses() as $class) {
                $this->invokeListeners($class);
            }

            $this->finalize();
        });
    }

    private function invokeListeners(\ReflectionClass $class): void
    {
        foreach ($this->listeners as $listener) {
            $listener->listen($class);
        }
    }

    private function finalize(): void
    {
        foreach ($this->listeners as $listener) {
            $listener->finalize();
        }

        $this->listeners = [];
    }
}
