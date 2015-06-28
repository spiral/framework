<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Core\Dispatcher\ClientException;

interface CoreInterface
{
    /**
     * Call controller method by fully specified or short controller name, action and addition
     * options such as default controllers namespace, default name and postfix.
     *
     * @param string $controller Controller name, or class, or name with namespace prefix.
     * @param string $action     Controller action, empty by default (controller will use default action).
     * @param array  $parameters Additional methods parameters.
     * @return mixed
     * @throws ClientException
     * @throws CoreException
     */
    public function callAction($controller, $action = '', array $parameters = []);
}