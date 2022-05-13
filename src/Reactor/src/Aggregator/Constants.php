<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\Constant;

/**
 * Constants aggregation. Can automatically create constant on demand.
 *
 * @method $this add(Constant $element)
 */
final class Constants extends Aggregator
{
    public function __construct(array $constants)
    {
        parent::__construct([Constant::class], $constants);
    }

    /**
     * Get named element by it's name.
     *
     * @return Constant|AggregableInterface
     */
    public function get(string $name): Constant
    {
        if (!$this->has($name)) {
            $constant = new Constant($name);
            $this->add($constant);

            return $constant;
        }

        return parent::get($name);
    }
}
