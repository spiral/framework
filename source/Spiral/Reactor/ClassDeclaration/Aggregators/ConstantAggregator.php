<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\ClassDeclaration\Aggregators;

use Spiral\Reactor\ClassElements\ConstantDeclaration;
use Spiral\Reactor\DeclarationAggregator;

/**
 * Constants aggregation. Can automatically create constant on demand.
 *
 * @method $this add(ConstantDeclaration $element)
 */
class ConstantAggregator extends DeclarationAggregator
{
    /**
     * @param array $constants
     */
    public function __construct(array $constants)
    {
        parent::__construct([ConstantDeclaration::class], $constants);
    }

    /**
     * Get named element by it's name.
     *
     * @param string $name
     * @return ConstantDeclaration
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            //Automatically creating constant
            $constant = new ConstantDeclaration($name, null);
            $this->add($constant);

            return $constant;
        }

        return parent::get($name);
    }
}