<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Auth\AuthContextInterface;
use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Cookies\Middleware\CookiesMiddleware;
use Spiral\Session\Middleware\SessionMiddleware;
use Spiral\Session\SessionInterface;

/**
 * The requested object depends on the {@see ServerRequestInterface} context.
 * Make sure that the related middleware was added to the pipeline and executed before
 * the object is requested from the container.
 * For example,
 * - {@see CookieQueue} requires {@see CookiesMiddleware}
 * - {@see SessionInterface} requires {@see SessionMiddleware}
 * - {@see AuthContextInterface} requires {@see AuthMiddleware}
 */
final class ContextualObjectNotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{
    public function __construct(string $id, ?string $key = null)
    {
        $keyStr = $key !== null ? " by the key `$key`" : '';
        parent::__construct("`$id` not found in Request attributes{$keyStr}.");
    }
}
