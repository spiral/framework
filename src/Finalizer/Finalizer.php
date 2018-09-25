<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Finalizer;

final class Finalizer implements FinalizerInterface
{
    /** @var callable[] */
    private $finalizers = [];

    /**
     * @inheritdoc
     */
    public function addFinalizer(callable $finalizer)
    {
        $this->finalizers[] = $finalizer;
    }

    /**
     * @inheritdoc
     */
    public function finalize()
    {
        foreach ($this->finalizers as $finalizer) {
            call_user_func($finalizer);
        }
    }
}