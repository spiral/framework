<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http\Exception;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container;
use Spiral\Core\Exception\Shared\InvalidContainerScopeException;
use Spiral\Framework\Spiral;

/**
 * The requested class depends on the {@see ServerRequestInterface} context which is not available in the
 * current container scope.
 * The {@see ServerRequestInterface} is usually available in the {@see Spiral::Http} or {@see Spiral::HttpRequest}
 * scopes.
 */
final class InvalidRequestScopeException extends InvalidContainerScopeException
{
    public function __construct(
        string $id,
        string|Container|null $scopeOrContainer = null,
        \Throwable|null $previous = null,
    ) {
        parent::__construct($id, $scopeOrContainer, Spiral::HttpRequest->value, $previous);
    }
}
