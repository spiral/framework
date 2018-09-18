<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Http\Error;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Does not render any page body.
 */
class NullRenderer implements RendererInterface
{
    /** @var ResponseFactoryInterface */
    private $responseFactory;

    /**
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param Request $request
     * @param int     $code
     * @param string  $message
     *
     * @return Response
     */
    public function renderException(Request $request, int $code, string $message): Response
    {
        return $this->responseFactory->createResponse($code);
    }
}