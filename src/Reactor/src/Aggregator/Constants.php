<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\Aggregator;
use Spiral\Reactor\DeclarationInterface;
use Spiral\Reactor\Partial\Constant;

/**
 * Constants aggregation. Can automatically create constant on demand.
 *
 * @method $this add(Constant $element)
 */
final class Constants extends Aggregator
{
    /**
     * @param array $constants
     */
    public function __construct(array $constants)
    {
        parent::__construct([Constant::class], $constants);
    }

    /**
     * Get named element by it's name.
     *
     * @param string $name
     * @return Constant|DeclarationInterface
     */
    public function get(string $name): Constant
    {
        if (!$this->has($name)) {
            $constant = new Constant($name, null);
            $this->add($constant);

            return $constant;
        }

        return parent::get($name);
    }
}
