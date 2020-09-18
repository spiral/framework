<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\Command;

use Spiral\Prototype\Exception\ClassNotDeclaredException;
use Spiral\Prototype\Injector;
use Symfony\Component\Console\Input\InputOption;

final class InjectCommand extends AbstractCommand
{
    public const NAME        = 'prototype:inject';
    public const DESCRIPTION = 'Inject all prototype dependencies';
    public const OPTIONS     = [
        ['remove', 'r', InputOption::VALUE_NONE, 'Remove PrototypeTrait']
    ];

    /**
     * Perform command.
     *
     * @throws \ReflectionException
     * @throws ClassNotDeclaredException
     */
    public function perform(): void
    {
        $prototyped = $this->locator->getTargetClasses();
        if ($prototyped === []) {
            $this->writeln('<comment>No prototyped classes found.</comment>');

            return;
        }

        $targets = [];

        foreach ($prototyped as $class) {
            $proto = $this->getPrototypeProperties($class);
            if (empty($proto)) {
                continue;
            }

            foreach ($proto as $target) {
                if ($target instanceof \Throwable) {
                    $targets[] = [$class->getName(), $target->getMessage(), $target->getFile(), $target->getLine()];
                    continue 2;
                }

                if ($target === null) {
                    continue 2;
                }
            }

            $targets[] = [$class->getName(), $this->mergeNames($proto), $this->mergeTargets($proto)];

            $classDefinition = $this->extractor->extract($class->getFilename(), $proto);

            try {
                $modified = (new Injector())->injectDependencies(
                    file_get_contents($class->getFileName()),
                    $classDefinition,
                    $this->option('remove')
                );

                file_put_contents($class->getFileName(), $modified);
            } catch (\Throwable $e) {
                $targets[] = [$class, $e->getMessage(), $e->getFile(), $e->getLine()];
            }
        }

        if (!empty($targets)) {
            $grid = $this->table(['Class:', 'Property:', 'Target:']);
            foreach ($targets as $target) {
                $grid->addRow($target);
            }

            $grid->render();
        }
    }
}
