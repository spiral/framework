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

    /** @var string */
    private $valueFormat;

    public function __construct(string $header = 'X-Auth-Token', string $valueFormat = '%s')
    {
        $this->header = $header;
        $this->valueFormat = $valueFormat;
    }

    /**
     * @inheritDoc
     */
    public function fetchToken(Request $request): ?string
    {
        if ($request->hasHeader($this->header)) {
            return $this->extractToken($request);
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
        if ($request->hasHeader($this->header) && $this->extractToken($request) === $tokenID) {
            return $response;
        }

        return $response->withAddedHeader($this->header, sprintf($this->valueFormat, $tokenID));
    }

    /**
     * @inheritDoc
     */
    public function removeToken(Request $request, Response $response, string $tokenID): Response
    {
        return $response;
    }

    private function extractToken(Request $request): ?string
    {
        $headerLine = $request->getHeaderLine($this->header);

        if ($this->valueFormat !== '%s') {
            [$token] = sscanf($headerLine, $this->valueFormat);

            return $token !== null ? (string) $token : null;
        }

        return $headerLine;
    }
}
