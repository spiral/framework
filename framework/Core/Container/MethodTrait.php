<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Container;

use Spiral\Core\Core;

trait MethodTrait
{
    /**
     * Helper method used to call object method with automatic parameters resolution using Container.
     *
     * @param string $method     Method name.
     * @param array  $parameters Set of parameters to populate.
     * @return mixed
     */
    protected function callMethod($method = '', array $parameters = array())
    {
        $reflection = new \ReflectionMethod($this, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($this, Core::getInstance()->resolveArguments($reflection, $parameters));
    }
}