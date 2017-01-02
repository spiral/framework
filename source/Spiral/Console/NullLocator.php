<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Console;

class NullLocator implements LocatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function locateCommands(): array
    {
        return [];
    }
}