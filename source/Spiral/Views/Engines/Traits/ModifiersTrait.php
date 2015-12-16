<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Engines\Traits;

use Spiral\Core\ContainerInterface;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\ModifierInterface;

/**
 * Provides ability to modify view source before giving it to template engine.
 */
trait ModifiersTrait
{
    /**
     * Modifier class names.
     *
     * @var array|ModifierInterface[]
     */
    protected $modifiers = [];

    /**
     * @var EnvironmentInterface
     */
    protected $environment = null;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * Initiate set of modifiers.
     *
     * @return ModifierInterface[]
     */
    protected function getModifiers()
    {
        foreach ($this->modifiers as $index => $modifier) {
            if (!is_object($modifier)) {
                //Initiating using container
                $this->modifiers[$index] = $this->container->make($modifier, [
                    'environment' => $this->environment
                ]);
            }
        }

        return $this->modifiers;
    }
}