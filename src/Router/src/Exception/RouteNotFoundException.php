<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router\Exception;

use Throwable;
use Psr\Http\Message\UriInterface;

class RouteNotFoundException extends UndefinedRouteException
{
    private UriInterface $uri;

    /**
     * @param Throwable|null $previous
     */
    public function __construct(UriInterface $uri, int $code = 0, Throwable $previous = null)
    {
        $this->uri = $uri;
        parent::__construct(sprintf('Unable to route `%s`.', $uri), $code, $previous);
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }
}
