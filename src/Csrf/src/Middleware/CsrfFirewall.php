<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Csrf\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Provides generic CSRF protection using cookie as token storage. Set "csrfToken" attribute to
 * request.
 */
final class CsrfFirewall implements MiddlewareInterface
{
    /**
     * Header to check for token instead of POST/GET data.
     */
    public const HEADER = 'X-CSRF-Token';

    /**
     * Parameter name used to represent client token in POST data.
     */
    public const PARAMETER = 'csrf-token';

    /**
     * Methods to be allowed to be passed with proper token.
     */
    public const ALLOW_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    /** @var ResponseFactoryInterface */
    private $responseFactory;

    /** @var array */
    private $allowMethods;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param array                    $allowMethods
     */
    public function __construct(ResponseFactoryInterface $responseFactory, array $allowMethods = self::ALLOW_METHODS)
    {
        $this->responseFactory = $responseFactory;
        $this->allowMethods = $allowMethods;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $token = $request->getAttribute(CsrfMiddleware::ATTRIBUTE);

        if (empty($token)) {
            throw new \LogicException('Unable to apply CSRF firewall, attribute is missing');
        }

        if ($this->isRequired($request) && !hash_equals($token, $this->fetchToken($request))) {
            return $this->responseFactory->createResponse(412, 'Bad CSRF Token');
        }

        return $handler->handle($request);
    }

    /**
     * Check if middleware should validate csrf token.
     *
     * @param Request $request
     * @return bool
     */
    protected function isRequired(Request $request): bool
    {
        return !in_array($request->getMethod(), $this->allowMethods, true);
    }

    /**
     * Fetch token from request.
     *
     * @param Request $request
     * @return string
     */
    protected function fetchToken(Request $request): string
    {
        if ($request->hasHeader(self::HEADER)) {
            return (string)$request->getHeaderLine(self::HEADER);
        }

        $data = $request->getParsedBody();
        if (is_array($data) && isset($data[self::PARAMETER]) && is_string($data[self::PARAMETER])) {
            return $data[self::PARAMETER];
        }

        return '';
    }
}
