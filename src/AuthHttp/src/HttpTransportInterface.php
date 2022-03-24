<?php

declare(strict_types=1);

namespace Spiral\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Provides the ability to read and write token values using PSR-7 Request/Response.
 */
interface HttpTransportInterface
{
    /**
     * Fetch tokenID from incoming request.
     */
    public function fetchToken(Request $request): ?string;

    /**
     * Commit (write) token to the outgoing response.
     */
    public function commitToken(
        Request $request,
        Response $response,
        string $tokenID,
        \DateTimeInterface $expiresAt = null
    ): Response;

    /**
     * Remove token from the outgoing response.
     */
    public function removeToken(Request $request, Response $response, string $tokenID): Response;
}
