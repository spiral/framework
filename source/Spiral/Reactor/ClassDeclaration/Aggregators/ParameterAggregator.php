<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\ClassDeclaration\Aggregators;

use Spiral\Reactor\ClassDeclaration\ParameterDeclaration;
use Spiral\Reactor\DeclarationAggregator;

/**
 * Constants aggregation. Can automatically create constant on demand.
 *
 * @method $this add(ParameterDeclaration $element)
 */
class ParameterAggregator extends DeclarationAggregator
{
    /**
     * @param array $constants
     */
    public function __construct(array $constants)
    {
        parent::__construct([ParameterDeclaration::class], $constants);
    }

    /**
     * Get named element by it's name.
     *
     * @param string $name
     * @return ParameterDeclaration
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            //Automatically creating constant
            $parameter = new ParameterDeclaration($name, null);
            $this->add($parameter);

            return $parameter;
        }

        return parent::get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function render($indentLevel = 0)
    {
        /**
         * Overwriting parent call.
         */

        $parameters = [];
        foreach ($this->getIterator() as $element) {
            $parameters[] = $element->render(0);
        }

        return join(', ', $parameters);
    }
}