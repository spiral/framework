<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace TestApplication\Controllers;

use Models\Hybrid;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Controller;
use Spiral\Http\Routing\RouteInterface;
use Spiral\Translator\Traits\TranslatorTrait;

class DummyController extends Controller
{
    use TranslatorTrait;

    public function indexAction(string $name = 'John')
    {
        return "Hello, {$name}.";
    }

    public function routeAction(RouteInterface $route)
    {
        return $route->getName();
    }

    public function requiredAction(int $id)
    {
        $this->say('Hello world');

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