<?php

declare(strict_types=1);

namespace Spiral\Prototype\Command;

use Spiral\Console\Command;
use Spiral\Prototype\Dependency;
use Spiral\Prototype\NodeExtractor;
use Spiral\Prototype\PropertyExtractor;
use Spiral\Prototype\PrototypeLocator;
use Spiral\Prototype\PrototypeRegistry;
use Throwable;

abstract class AbstractCommand extends Command
{
    private array $cache = [];

    public function __construct(
        protected readonly PrototypeLocator $locator,
        protected readonly NodeExtractor $extractor
    ) {
        parent::__construct();
    }

    /**
     * Fetch class dependencies.
     *
     * @return array<array-key, Dependency|Throwable|null>
     */
    protected function getPrototypeProperties(\ReflectionClass $class, array $all = []): array
    {
        /** @var array<int, array<non-empty-string, Dependency|Throwable|null>> $results */
        $results = [$this->readProperties($class)];

        $parent = $class->getParentClass();
        while ($parent instanceof \ReflectionClass && isset($all[$parent->getName()])) {
            $results[] = $this->readProperties($parent);
            $parent = $parent->getParentClass();
        }

        return \iterator_to_array($this->reverse($results));
    }

    protected function getExtractor(): PropertyExtractor
    {
        return $this->container->get(PropertyExtractor::class);
    }

    protected function getRegistry(): PrototypeRegistry
    {
        return $this->container->get(PrototypeRegistry::class);
    }

    /**
     * @param non-empty-array<array-key, Dependency|Throwable|null> $properties
     */
    protected function mergeNames(array $properties): string
    {
        return \implode("\n", \array_keys($properties));
    }

    /**
     * @param non-empty-array<array-key, Dependency|Throwable|null> $properties
     */
    protected function mergeTargets(array $properties): string
    {
        $result = [];

        foreach ($properties as $target) {
            if ($target instanceof Throwable) {
                $result[] = \sprintf(
                    '<fg=red>%s [f: %s, l: %s]</fg=red>',
                    $target->getMessage(),
                    $target->getFile(),
                    $target->getLine()
                );
                continue;
            }

            if ($target === null) {
                $result[] = '<fg=yellow>undefined</fg=yellow>';
                continue;
            }

            $result[] = $target->type->fullName;
        }

        return \implode("\n", $result);
    }

    /**
     * @return array<non-empty-string, Dependency|Throwable|null>
     */
    private function readProperties(\ReflectionClass $class): array
    {
        if (isset($this->cache[$class->getFileName()])) {
            $proto = $this->cache[$class->getFileName()];
        } else {
            $proto = $this->getExtractor()->getPrototypeProperties(\file_get_contents($class->getFileName()));
            $this->cache[$class->getFileName()] = $proto;
        }

        $result = [];
        foreach ($proto as $name) {
            if (!isset($result[$name])) {
                $result[$name] = $this->getRegistry()->resolveProperty($name);
            }
        }

        return $result;
    }

    /**
     * @template T
     * @template TK
     * @param array<array-key, array<TK, T>> $results
     *
     * @return \Generator<TK, T>
     */
    private function reverse(array $results): \Generator
    {
        foreach (\array_reverse($results) as $result) {
            yield from $result;
        }
    }
}
