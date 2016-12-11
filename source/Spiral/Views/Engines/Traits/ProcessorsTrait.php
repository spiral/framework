<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Engines\Traits;

use Spiral\Core\FactoryInterface;
use Spiral\Views\ModifierInterface;

/**
 * Provides ability to modify view source before giving it to template engine.
 *
 * @todo: do i need it?
 */
trait ProcessorsTrait
{
    /**
     * Modifier class names.
     *
     * @var array|ModifierInterface[]
     */
    protected $modifiers = [];

    /**
     * Initiate set of modifiers.
     *
     * @return ModifierInterface[]
     */
    protected function getModifiers(FactoryInterface $factory)
    {
        foreach ($this->modifiers as $index => $modifier) {
            if (!is_object($modifier)) {
                //Initiating using container
                $this->modifiers[$index] = $factory->make($modifier);
            }
        }

        return $this->modifiers;
    }
}