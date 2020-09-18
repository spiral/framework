<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\Command;

final class ListCommand extends AbstractCommand
{
    public const NAME        = 'prototype:list';
    public const DESCRIPTION = 'List all prototyped classes';

    /**
     * List all prototype classes.
     */
    public function perform(): void
    {
        $prototyped = $this->locator->getTargetClasses();
        if ($prototyped === []) {
            $this->writeln('<comment>No prototyped classes found.</comment>');

            return;
        }

        $grid = $this->table(['Class:', 'Property:', 'Target:']);

        foreach ($prototyped as $class) {
            $proto = $this->getPrototypeProperties($class);

            $grid->addRow([$class->getName(), $this->mergeNames($proto), $this->mergeTargets($proto)]);
        }

        $grid->render();
    }
}
