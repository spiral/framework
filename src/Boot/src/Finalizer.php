<?php

declare(strict_types=1);

namespace Spiral\Boot;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Boot\Event\Finalizing;
use Spiral\Events\EventDispatcherAwareInterface;

final class Finalizer implements FinalizerInterface, EventDispatcherAwareInterface
{
    private ?EventDispatcherInterface $dispatcher = null;

    /** @var callable[] */
    private array $finalizers = [];

    public function addFinalizer(callable $finalizer): static
    {
        $this->finalizers[] = $finalizer;

        return $this;
    }

    public function finalize(bool $terminate = false): void
    {
        $this->dispatcher?->dispatch(new Finalizing($this));

        foreach ($this->finalizers as $finalizer) {
            \call_user_func($finalizer, $terminate);
        }
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->dispatcher = $eventDispatcher;
    }
}
