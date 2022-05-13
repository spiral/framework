<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\FunctionDeclaration;

/**
 * TraitUses aggregation. Can automatically create TraitUse on demand.
 *
 * @method $this add(FunctionDeclaration $element)
 */
final class Functions extends Aggregator
{
    public function __construct(array $functions)
    {
        parent::__construct([FunctionDeclaration::class], $functions);
    }

    /**
     * Get named element by it's name.
     *
     * @return FunctionDeclaration|AggregableInterface
     */
    public function get(string $name): FunctionDeclaration
    {
        if (!$this->has($name)) {
            $function = new FunctionDeclaration($name);
            $this->add($function);

            return $function;
        }

        return parent::get($name);
    }
}
