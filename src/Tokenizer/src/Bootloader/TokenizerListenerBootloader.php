<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Bootloader;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;

final class TokenizerListenerBootloader extends Bootloader implements SingletonInterface
{
    protected const DEPENDENCIES = [
        TokenizerBootloader::class,
    ];

    /** @var TokenizationListenerInterface[] */
    private array $listeners = [];

    public function addListener(TokenizationListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function boot(AbstractKernel $kernel, ClassesInterface $classes): void
    {
        $kernel->bootstrapped(function () use ($classes) {
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