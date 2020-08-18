<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Boot;

final class Finalizer implements FinalizerInterface
{
    /** @var callable[] */
    private $finalizers = [];

    /**
     * @inheritdoc
     */
    public function addFinalizer(callable $finalizer): void
    {
        $this->finalizers[] = $finalizer;
    }

    /**
     * @inheritdoc
     */
    public function finalize(bool $terminate = false): void
    {
        foreach ($this->finalizers as $finalizer) {
            call_user_func($finalizer, $terminate);
        }
    }
}
