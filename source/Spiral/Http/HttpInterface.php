<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Http\Exceptions\ClientException;

/**
 * Represent simple http abstraction layer.
 */
interface HttpInterface
{
    /**
     * Execute request using internal http logic.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws ClientException
     */
    public function perform(ServerRequestInterface $request, ResponseInterface $response = null);

    /**
     * Dispatch response to client.
     *
     * @param ResponseInterface $response
     */
    public function dispatch(ResponseInterface $response);
}