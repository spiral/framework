<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\GRPC;

/**
 * @deprecated since v2.12. Will be removed in v3.0
 */
interface LocatorInterface
{
    /**
     * Return list of available GRPC services in a form of [interface => object].
     *
     * @return array
     */
    public function getServices(): array;
}
