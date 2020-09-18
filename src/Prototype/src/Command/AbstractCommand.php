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

abstract class AbstractCommand extends Command
{
    /** @var PrototypeLocator */
    protected $locator;

    /** @var NodeExtractor */
    protected $extractor;

    /** @var PrototypeRegistry */
    protected $registry;

    /**
     * @param PrototypeLocator  $locator
     * @param NodeExtractor     $extractor
     * @param PrototypeRegistry $registry
     */
    public function __construct(PrototypeLocator $locator, NodeExtractor $extractor, PrototypeRegistry $registry)
    {
        parent::__construct(null);

        $this->extractor = $extractor;
        $this->locator = $locator;
        $this->registry = $registry;
    }

    /**
     * Fetch class dependencies.
     *
     * @param \ReflectionClass $class
     * @return string[]
     */
    protected function getPrototypeProperties(\ReflectionClass $class): array
    {
        $proto = $this->getExtractor()->getPrototypeProperties(file_get_contents($class->getFilename()));

        $result = [];
        foreach ($proto as $name) {
            $result[$name] = $this->registry->resolveProperty($name);
        }

        return $result;
    }

    /**
     * @return PropertyExtractor
     */
    protected function getExtractor(): PropertyExtractor
    {
        return $this->container->get(PropertyExtractor::class);
    }

    /**
     * @param Dependency[] $properties
     * @return string
     */
    protected function mergeNames(array $properties): string
    {
        return join("\n", array_keys($properties));
    }

    /**
     * @param Dependency[] $properties
     * @return string
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

        return join("\n", $result);
    }
}
