<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http;

use Psr\Http\Message\ResponseInterface;
use Spiral\Core\Component;
use Spiral\Core\Container;

class MiddlewarePipe extends Component
{
    /**
     * Set of middleware layers builded to handle incoming Request and return Response. Middleware
     * can be represented as class, string (DI) or array (callable method).
     *
     * @var array|MiddlewareInterface[]
     */
    protected $middleware = array();

    /**
     * Final endpoint has to be called, this is "the deepest" part of pipeline. It's not necessary
     * that this endpoint will be called at all, as one of middleware layers can stop processing.
     *
     * @var callable
     */
    protected $final = null;

    protected $context = null;

    public function __construct($m = array())
    {
        $this->middleware = $m;
    }

    public function add($m)
    {
        $this->middleware[] = $m;
    }

    public function target($x)
    {
        $this->final = $x;

        return $this;
    }

    public function run($input, $context = null)
    {
        $this->context = $context;

        return $this->next(0, $input);
    }

    protected function next($position = 0, $input = null)
    {
        $next = function ($contextInput = null) use ($position, $input)
        {
            return $this->next(++$position, $contextInput ?: $input);
        };

        if (!isset($this->middleware[$position]))
        {
            return call_user_func($this->final, $input);
        }

        $middleware = $this->middleware[$position];

        if (is_string($middleware))
        {
            $middleware = Container::get($middleware);
        }

        /**
         * @var callable $middleware
         */

        return $middleware($input, $next, $this->context);
    }
}