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
use Spiral\Reactor\Partial\Property;

/**
 * Property aggregation. Can automatically create constant on demand.
 *
 * @method $this add(Property $element)
 */
final class Properties extends Aggregator
{
    public function __construct(array $constants)
    {
        parent::__construct([Property::class], $constants);
    }

    /**
     * Get named element by it's name.
     *
     * @return Property|DeclarationInterface
     */
    public function get(string $name): Property
    {
        if (!$this->has($name)) {
            //Automatically creating constant
            $property = new Property($name);
            $this->add($property);

            return $property;
        }

        return parent::get($name);
    }
}
