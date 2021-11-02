<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Http\ErrorHandler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Http\Header\AcceptHeader;

/**
 * Does not render any page body.
 */
final class PlainRenderer implements RendererInterface
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
     * @return Response
     */
    public function renderException(Request $request, int $code, string $message): Response
    {
        $acceptItems = AcceptHeader::fromString($request->getHeaderLine('Accept'))->getAll();

        $response = $this->responseFactory->createResponse($code);
        if ($acceptItems && $acceptItems[0]->getValue() === 'application/json') {
            $response = $response->withHeader('Content-Type', 'application/json; charset=UTF-8');
            $response->getBody()->write(json_encode(['status' => $code, 'error' => $message]));
        } else {
            $response->getBody()->write("Error code: {$code}");
        }

        return $response;
    }
}
