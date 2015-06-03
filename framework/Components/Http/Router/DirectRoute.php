<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Router;

class DirectRoute extends AbstractRoute
{
    protected $namespace = '';

    protected $postfix = '';

    protected $controllers = array();


    public function controllers(array $controllers)
    {
        $this->controllers += $controllers;

        return $this;
    }
}