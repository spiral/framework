<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor;

/**
 * Declaration with name.
 */
interface NamedInterface extends DeclarationInterface
{
    /**
     * @return string
     */
    public function getName(): string;
}
