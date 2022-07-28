<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\Command;

use Spiral\Console\Command;
use Spiral\Prototype\Dependency;
use Spiral\Prototype\NodeExtractor;
use Spiral\Prototype\PropertyExtractor;
use Spiral\Prototype\PrototypeLocator;
use Spiral\Prototype\PrototypeRegistry;
use Psr\Container\ContainerExceptionInterface;

abstract class AbstractCommand extends Command
{
    /** @var PrototypeLocator */
    protected $locator;

    /** @var NodeExtractor */
    protected $extractor;

    /** @var PrototypeRegistry */
    protected $registry;

    private array $cache = [];

    public function __construct(PrototypeLocator $locator, NodeExtractor $extractor, PrototypeRegistry $registry)
    {
        parent::__construct();

        $this->extractor = $extractor;
        $this->locator = $locator;
        $this->registry = $registry;
    }

    /**
     * Fetch class dependencies.
     *
     * @return null[]|Dependency[]|\Throwable[]
     */
    protected function getPrototypeProperties(\ReflectionClass $class, array $all = []): array
    {
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

    /**
     * @param Dependency[] $properties
     */
    protected function mergeNames(array $properties): string
    {
        return implode("\n", array_keys($properties));
    }

    /**
     * @param Dependency[] $properties
     */
    protected function mergeTargets(array $properties): string
    {
        $result = [];

        foreach ($properties as $target) {
            if ($target instanceof \Throwable) {
                $result[] = sprintf(
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

        return implode("\n", $result);
    }

    /**
     * @return array<string, Dependency|ContainerExceptionInterface|null>
     */
    private function readProperties(\ReflectionClass $class): array
    {
        if (isset($this->cache[$class->getFileName()])) {
            $proto = $this->cache[$class->getFileName()];
        } else {
            $proto = $this->getExtractor()->getPrototypeProperties(file_get_contents($class->getFilename()));
            $this->cache[$class->getFileName()] = $proto;
        }

        $result = [];
        foreach ($proto as $name) {
            if (!isset($result[$name])) {
                $result[$name] = $this->registry->resolveProperty($name);
            }
        }

        return $result;
    }

    /**
     * @param null[]|Dependency[]|\Throwable[] $results
     *
     * @return \Generator<array-key, null|Dependency|\Throwable, mixed, void>
     */
    private function reverse(array $results): \Generator
    {
        foreach (\array_reverse($results) as $result) {
            yield from $result;
        }
    }
}
