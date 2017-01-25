<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace TestApplication\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Controller;
use Spiral\Translator\Traits\TranslatorTrait;

class DummyController extends Controller
{
    use TranslatorTrait;

    public function indexAction(string $name = 'John')
    {
        return "Hello, {$name}.";
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