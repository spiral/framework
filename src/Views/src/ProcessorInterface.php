<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Views;

interface ProcessorInterface
{
    /**
     * Process given view source and return new version with altered code.
     */
    public function process(ViewSource $source, ContextInterface $context): ViewSource;
}
