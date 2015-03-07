<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Middleware;

use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\RequestInterface as PsrRequest;

interface MiddlewareInterface
{
    /**
     * @param PsrRequest $request
     * @param \Closure   $next
     * @return PsrResponse
     */
    public function handle(PsrRequest $request, \Closure $next = null);
}