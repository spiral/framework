<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace TestApplication\Controllers;

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

    static function innerAction()
    {

    }
}