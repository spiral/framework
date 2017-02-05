<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace TestApplication\Controllers;

use Spiral\Core\HMVC\ControllerInterface;

//Simply response with given action and parameters
class MagicController implements ControllerInterface
{
    public function callAction(string $action = null, array $parameters = [])
    {
        return $action . ':' . json_encode($parameters);
    }
}