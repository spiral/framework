<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace TestApplication\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Controller;
use Spiral\Http\Routing\RouteInterface;
use Spiral\Translator\Traits\TranslatorTrait;

class DummyController extends Controller
{
    use TranslatorTrait;

    public function indexAction(string $name = 'Dave')
    {
        return "Hello, {$name}.";
    }

    public function routeAction(RouteInterface $route)
    {
        return $route->getName();
    }

    public function matchesAction()
    {
        return $this->route->getMatches();
    }

    public function requiredAction(int $id)
    {
        //no index
        $this->say(get_class($this));

        $this->say('Hello world');
        $this->say('Hello world', [], 'external');

        l('l');
        l('l', [], 'external');

        p('%s unit|%s units', 10);
        p('%s unit|%s units', 10, [], 'external');

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