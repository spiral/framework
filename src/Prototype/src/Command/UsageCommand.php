<?php

declare(strict_types=1);

namespace Spiral\Prototype\Command;

final class UsageCommand extends AbstractCommand
{
    public const NAME = 'prototype:usage';
    public const DESCRIPTION = 'List all prototyped classes';

    /**
     * List all prototype classes.
     */
    public function perform(): int
    {
        $prototyped = $this->locator->getTargetClasses();
        if ($prototyped === []) {
            $this->writeln('<comment>No prototyped classes found.</comment>');

            return self::SUCCESS;
        }

        $grid = $this->table(['Class:', 'Property:', 'Target:']);

        foreach ($prototyped as $class) {
            $proto = $this->getPrototypeProperties($class, $prototyped);

            $grid->addRow([$class->getName(), $this->mergeNames($proto), $this->mergeTargets($proto)]);
        }

        $grid->render();

        return self::SUCCESS;
    }
}
