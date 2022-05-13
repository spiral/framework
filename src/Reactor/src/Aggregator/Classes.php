<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\ClassDeclaration;

/**
 * TraitUses aggregation. Can automatically create TraitUse on demand.
 *
 * @method $this add(ClassDeclaration $element)
 */
final class Classes extends Aggregator
{
    public function __construct(array $classes)
    {
        parent::__construct([ClassDeclaration::class], $classes);
    }

    /**
     * Get named element by it's name.
     *
     * @return ClassDeclaration|AggregableInterface
     */
    public function get(string $name): ClassDeclaration
    {
        if (!$this->has($name)) {
            $class = new ClassDeclaration($name);
            $this->add($class);

            return $class;
        }

        return parent::get($name);
    }
}
