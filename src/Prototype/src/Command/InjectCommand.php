<?php

declare(strict_types=1);

namespace Spiral\Prototype\Command;

use Spiral\Prototype\Exception\ClassNotDeclaredException;
use Spiral\Prototype\Injector;
use Spiral\Prototype\NodeExtractor;
use Spiral\Prototype\PrototypeLocator;
use Symfony\Component\Console\Input\InputOption;

final class InjectCommand extends AbstractCommand
{
    public const NAME = 'prototype:inject';
    public const DESCRIPTION = 'Inject all prototype dependencies';
    public const OPTIONS = [
        ['remove', 'r', InputOption::VALUE_NONE, 'Remove PrototypeTrait'],
    ];

    private readonly Injector $injector;

    public function __construct(PrototypeLocator $locator, NodeExtractor $extractor)
    {
        parent::__construct($locator, $extractor);
        $this->injector = new Injector();
    }

    /**
     * Perform command.
     *
     * @throws \ReflectionException
     * @throws ClassNotDeclaredException
     */
    public function perform(): int
    {
        $prototyped = $this->locator->getTargetClasses();
        if ($prototyped === []) {
            $this->writeln('<comment>No prototyped classes found.</comment>');

            return self::SUCCESS;
        }

        $targets = [];

        foreach ($prototyped as $class) {
            $proto = $this->getPrototypeProperties($class, $prototyped);
            if (empty($proto)) {
                $modified = $this->modify($class, $proto);
                if ($modified !== null) {
                    $targets[] = $modified;
                }
                continue;
            }

            foreach ($proto as $target) {
                if ($target instanceof \Throwable) {
                    $targets[] = [
                        $class->getName(),
                        $target->getMessage(),
                        \sprintf('%s:L%d', $target->getFile(), $target->getLine()),
                    ];
                    continue 2;
                }

                if ($target === null) {
                    continue 2;
                }
            }

            $targets[] = [$class->getName(), $this->mergeNames($proto), $this->mergeTargets($proto)];

            $modified = $this->modify($class, $proto);
            if ($modified !== null) {
                $targets[] = $modified;
            }
        }

        if (!empty($targets)) {
            $grid = $this->table(['Class:', 'Property:', 'Target:']);
            foreach ($targets as $target) {
                $grid->addRow($target);
            }

            $grid->render();
        }

        return self::SUCCESS;
    }

    private function modify(\ReflectionClass $class, array $proto): ?array
    {
        $classDefinition = $this->extractor->extract($class->getFileName(), $proto);
        try {
            $modified = $this->injector->injectDependencies(
                \file_get_contents($class->getFileName()),
                $classDefinition,
                $this->option('remove')
            );

            \file_put_contents($class->getFileName(), $modified);

            return null;
        } catch (\Throwable $e) {
            return [$class->getName(), $e->getMessage(), \sprintf('%s:L%s', $e->getFile(), $e->getLine())];
        }
    }
}
