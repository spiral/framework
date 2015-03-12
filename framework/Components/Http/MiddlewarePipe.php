<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http;

use Spiral\Core\Component;

class MiddlewarePipe extends Component
{
    protected $m = array();

    protected $target = null;

    public function __construct($m = array())
    {
        $this->m = $m;
    }

    public function add($m)
    {
        $this->m[] = $m;
    }

    public function target($x)
    {
        $this->target = $x;

        return $this;
    }

    public function run($input)
    {
        return $this->next(0, $input);
    }

    protected function next($position = 0, $input = null)
    {
        $next = function ($contextInput = null) use ($position, $input)
        {
            return $this->next(++$position, $contextInput ?: $input);
        };

        if (!isset($this->m[$position]))
        {
            return call_user_func($this->target, $input);
        }

        return $this->m[$position]($input, $next, $this);
    }
}