<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace TestApplication\Controllers;

use Spiral\Core\HMVC\ControllerInterface;
use function GuzzleHttp\json_encode;

//Simply response with given action and parameters
class MagicController implements ControllerInterface
{
    public function callAction(string $action = null, array $parameters = [])
    {
        return $action . ':' . json_encode($parameters);
    }
}