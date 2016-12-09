<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Http\Exceptions\ClientException;

/**
 * Represent simple http abstraction layer.
 */
interface HttpInterface
{
    /**
     * Execute request using internal http logic.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     * @throws ClientException
     */
    public function perform(Request $request, Response $response = null): Response;

    /**
     * Dispatch response to client.
     *
     * @param Response $response
     */
    public function dispatch(Response $response);
}