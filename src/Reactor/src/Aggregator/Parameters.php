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
use Spiral\Reactor\Partial\Parameter;

/**
 * Constants aggregation. Can automatically create constant on demand.
 *
 * @method $this add(Parameter $element)
 */
final class Parameters extends Aggregator
{
    /**
     * @param array $constants
     */
    public function __construct(array $constants)
    {
        parent::__construct([Parameter::class], $constants);
    }

    /**
     * Get named element by it's name.
     *
     * @param string $name
     * @return Parameter|DeclarationInterface
     */
    public function get(string $name): Parameter
    {
        if (!$this->has($name)) {
            //Automatically creating constant
            $parameter = new Parameter($name);
            $this->add($parameter);

            return $parameter;
        }

        return parent::get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function render(int $indentLevel = 0): string
    {
        /**
         * Overwriting parent call.
         */
        $parameters = [];
        foreach ($this->getIterator() as $element) {
            $parameters[] = $element->render(0);
        }

        return implode(', ', $parameters);
    }
}
