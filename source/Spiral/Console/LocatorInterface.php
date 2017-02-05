<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Console;

interface LocatorInterface
{
    /**
     * Locate all available command class names.
     *
     * @return array
     */
    public function locateCommands(): array;
}