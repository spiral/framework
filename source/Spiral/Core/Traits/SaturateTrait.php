<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Traits;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Spiral\Core\Exceptions\SugarException;

/**
 * Saturate optional constructor or method argument (class) using internal (usually static)
 * container. In most of cases trait is doing nothing since spiral Container populates even
 * optional class dependencies.
 *
 * Avoid using this trait in custom code, it's only a development sugar.
 */
trait SaturateTrait
{
    /**
     * Must be used only to resolve optional constructor arguments. Use in classes which are
     * generally resolved using Container. Default value MUST always be supplied from outside.
     *
     * @param mixed  $default Default value.
     * @param string $class   Requested class.
     * @return mixed|null|object
     * @throws SugarException
     */
    private function saturate($default, $class)
    {
        if (!empty($default)) {
            return $default;
        }

        //Only when global container is set
        try {
            return $this->container()->get($class);
        } catch (ContainerException $exception) {
            throw new SugarException(
                "Unable to saturate '{$class}': {$exception->getMessage()}", 0, $exception
            );
        }
    }

    /**
     * Class specific container.
     *
     * @return ContainerInterface
     */
    abstract protected function container();
}
