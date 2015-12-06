<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\ClassDeclaration\Aggregators;

use Spiral\Reactor\ClassDeclaration\PropertyDeclaration;
use Spiral\Reactor\DeclarationAggregator;

/**
 * Property aggregation. Can automatically create constant on demand.
 *
 * @method $this add(PropertyDeclaration $element)
 */
class PropertyAggregator extends DeclarationAggregator
{
    /**
     * @param array $constants
     */
    public function __construct(array $constants)
    {
        parent::__construct([PropertyDeclaration::class], $constants);
    }

    /**
     * Get named element by it's name.
     *
     * @param string $name
     * @return PropertyDeclaration
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            //Automatically creating constant
            $property = new PropertyDeclaration($name);
            $this->add($property);

            return $property;
        }

        return parent::get($name);
    }
}