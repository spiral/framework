<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Auth\Middleware\Firewall;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Throws given exception if user is not authenticated.
 */
final class ExceptionFirewall extends AbstractFirewall
{
    /** @var \Throwable */
    private $e;

    public function __construct(\Throwable $e)
    {
        $this->e = $e;
    }

    /**
     * @inheritDoc
     *
     * @throws \Throwable
     */
    protected function denyAccess(Request $request, RequestHandlerInterface $handler): Response
    {
        throw $this->e;
    }
}
