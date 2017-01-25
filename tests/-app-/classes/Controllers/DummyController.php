<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace TestApplication\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Controller;

class DummyController extends Controller
{
    public function indexAction(string $name = 'John')
    {
        return "Hello, {$name}.";
    }

    public function requiredAction(int $id)
    {
        return $id;
    }

    public function scopedAction()
    {
        return spl_object_hash($this->container->get(ServerRequestInterface::class));
    }

    static function innerAction()
    {

    }
}