<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\HMVC;

use Spiral\Core\Exceptions\ControllerException;

/**
 * General application enterpoint class.
 */
interface CoreInterface
{
    /**
     * Request specific action result from Core. Due in 99% every action will need parent
     * controller, we can request it too.
     *
     * @param string $controller Controller class.
     * @param string $action Controller action, empty by default (controller will use default
     *                           action).
     * @param array $parameters Action parameters (if any).
     * @return mixed
     * @throws ControllerException
     * @throws \Exception
     */
    public function callAction($controller, $action = '', array $parameters = []);
}