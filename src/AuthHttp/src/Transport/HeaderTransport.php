<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Auth\Transport;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Auth\HttpTransportInterface;

/**
 * Reads and writes auth tokens via headers.
 */
final class HeaderTransport implements HttpTransportInterface
{
    /** @var string */
    private $header;

    /**
     * @param string $header
     */
    public function __construct(string $header = 'X-Auth-Token')
    {
        $this->header = $header;
    }

    /**
     * @inheritDoc
     */
    public function fetchToken(Request $request): ?string
    {
        if ($request->hasHeader($this->header)) {
            return $request->getHeaderLine($this->header);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function commitToken(
        Request $request,
        Response $response,
        string $tokenID,
        \DateTimeInterface $expiresAt = null
    ): Response {
        if ($request->hasHeader($this->header) && $request->getHeaderLine($this->header) === $tokenID) {
            return $response;
        }

        return $response->withAddedHeader($this->header, $tokenID);
    }

    /**
     * @inheritDoc
     */
    public function removeToken(Request $request, Response $response, string $tokenID): Response
    {
        return $response;
    }
}
