<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http;

use Psr\Http\Message\RequestInterface;

interface MiddlewareInterface
{
    public function handle(RequestInterface $request, \Closure $next = null, $context = null);
}