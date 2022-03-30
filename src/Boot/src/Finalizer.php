<?php

declare(strict_types=1);

namespace Spiral\Boot;

final class Finalizer implements FinalizerInterface
{
    /** @var callable[] */
    private array $finalizers = [];

    public function addFinalizer(callable $finalizer): static
    {
        $this->finalizers[] = $finalizer;

        return $this;
    }

    public function finalize(bool $terminate = false): void
    {
        foreach ($this->finalizers as $finalizer) {
            \call_user_func($finalizer, $terminate);
        }
    }
}
