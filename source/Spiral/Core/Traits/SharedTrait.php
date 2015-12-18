<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Traits;

use Interop\Container\ContainerInterface as InteropContainer;
use Spiral\Core\Exceptions\Container\AutowireException;
use Spiral\Core\Exceptions\Container\ContainerException;
use Spiral\Core\Exceptions\SugarException;

/**
 * Trait provides access to set of shared components (using short bindings). You can create virtual
 * copies of this trait to let IDE know about your bindings (works in PHPStorm).
 *
 * Compatible with any Component class.
 *
 * Attention, to improve code testability do not use this trait when class do not have local
 * container, plus you are recommended to use short bindings for components only, do not share
 * business models using this way (use DI).
 *
 * @see Component
 */
trait SharedTrait
{
    /**
     * Shortcut to Container get method.
     *
     * @see ContainerInterface::get()
     * @param string $alias
     * @return mixed|null|object
     * @throws AutowireException
     * @throws SugarException
     */
    public function __get($alias)
    {
        if ($this->container()->has($alias)) {
            return $this->container()->get($alias);
        }

        throw new SugarException("Unable to get property binding '{$alias}'.");

        //no parent call, too dangerous
    }

    /**
     * @return InteropContainer
     * @throws SugarException
     */
    abstract protected function container();
}