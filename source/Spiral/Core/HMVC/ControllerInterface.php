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
 * Class being treated as controller.
 */
interface ControllerInterface
{
    /**
     * Execute specific controller action (method).
     *
     * @param string $action Action name, without postfixes and prefixes.
     * @param array $parameters Method parameters.
     * @return mixed
     * @throws ControllerException
     * @throws \Exception
     */
    public function callAction($action = '', array $parameters = []);
}