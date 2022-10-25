<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Bootloader;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

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
        $kernel->booted(function (ClassesInterface $classes): void {
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
