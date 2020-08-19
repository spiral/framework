<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Console;

interface LocatorInterface
{
    /**
     * Get all available command class names.
     *
     * @return \Symfony\Component\Console\Command\Command[]
     *
     * @throws \Spiral\Console\Exception\LocatorException
     */
    public function locateCommands(): array;
}
