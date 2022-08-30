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
use Spiral\Reactor\Partial\Method;

/**
 * Method aggregation. Can automatically create constant on demand.
 *
 * @method Method add(Method $element)
 */
final class Methods extends Aggregator
{
    public function __construct(array $constants)
    {
        parent::__construct([Method::class], $constants);
    }

    /**
     * Get named element by it's name.
     *
     * @return Method|DeclarationInterface
     */
    public function get(string $name): Method
    {
        if (!$this->has($name)) {
            //Automatically creating constant
            $method = new Method($name);
            $this->add($method);

            return $method;
        }

        return parent::get($name);
    }
}
