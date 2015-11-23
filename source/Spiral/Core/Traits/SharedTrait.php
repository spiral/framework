<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Traits;

use Interop\Container\ContainerInterface as InteropContainer;
use Spiral\Core\Exceptions\Container\ArgumentException;
use Spiral\Core\Exceptions\Container\InstanceException;

/**
 * Trait provides access to set of shared components (using short bindings). You can create virtual
 * copies of this trait to let IDE know about your bindings (works in PHPStorm).
 */
trait SharedTrait
{
    /**
     * @invisible
     * @var InteropContainer
     */
    protected $container = null;

    /**
     * Shortcut to Container get method.
     *
     * @see ContainerInterface::get()
     * @param string $alias
     * @return mixed|null|object
     * @throws InstanceException
     * @throws ArgumentException
     */
    public function __get($alias)
    {
        return $this->container->get($alias);
    }
}