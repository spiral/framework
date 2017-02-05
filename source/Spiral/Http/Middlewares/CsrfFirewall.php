<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Http\MiddlewareInterface;

/**
 * Provides generic CSRF protection using cookie as token storage. Set "csrfToken" attribute to
 * request.
 */
class CsrfFirewall implements MiddlewareInterface
{
    /**
     * Header to check for token instead of POST/GET data.
     */
    const HEADER = 'X-CSRF-Token';

    /**
     * Parameter name used to represent client token in POST data.
     */
    const PARAMETER = 'csrf-token';

    /**
     * Methods to be allowed to be passed with proper token.
     */
    const ALLOW_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $token = $request->getAttribute(CsrfMiddleware::ATTRIBUTE);

        if (empty($token)) {
            throw new \LogicException("Unable to apply CSRF firewall, attribute is missing");
        }

        if ($this->isRequired($request) && !hash_equals($token, $this->fetchToken($request))) {
            //Invalid CSRF token
            return $response->withStatus(412, 'Bad CSRF Token');
        }

        return $next($request, $response);
    }

    /**
     * Check if middleware should validate csrf token.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isRequired(Request $request): bool
    {
        return !in_array($request->getMethod(), static::ALLOW_METHODS);
    }

    /**
     * Fetch token from request.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function fetchToken(Request $request): string
    {
        if ($request->hasHeader(self::HEADER)) {
            return (string)$request->getHeaderLine(self::HEADER);
        }

             $data = $request->getParsedBody();
        if (is_array($data) && isset($data[self::PARAMETER])) {
            if (is_string($data[self::PARAMETER])) {
                return (string)$data[self::PARAMETER];
            }
        }

        return '';
    }
}