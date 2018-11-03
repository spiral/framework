<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Http\ErrorHandler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Render exception content into response.
 */
interface RendererInterface
{
    /**
     * @param Request $request
     * @param int     $code
     * @param string  $message
     *
     * @return Response
     */
    public function renderException(Request $request, int $code, string $message): Response;
}