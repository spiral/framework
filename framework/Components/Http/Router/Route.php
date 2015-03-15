<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Router;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Components\Http\MiddlewarePipe;
use Spiral\Core\Component;

class Route extends Component
{
    protected $middleware = array();


    public function getName(){
return 'a';
    }

    public function perform(ServerRequestInterface $request)
    {
        $pipeline = new MiddlewarePipe($this->middleware);

        return $pipeline->target(function (ServerRequestInterface $request)
        {
            return $this->execute($request);
        })->run($request, $this);
    }

    protected function execute(ServerRequestInterface $request)
    {
        return 'abc';
    }
}