<?php

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Attribute\Scope;
use Spiral\Core\Exception\ScopeException;
use Spiral\Core\FactoryInterface;
use Spiral\Framework\Spiral;
use Spiral\Pagination\PaginationProviderInterface;
use Spiral\Pagination\Paginator;
use Spiral\Pagination\PaginatorInterface;

/**
 * Paginators factory binded to active request scope in order to select page number.
 */
#[Scope(Spiral::HttpRequest)]
final class PaginationFactory implements PaginationProviderInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly FactoryInterface $factory
    ) {
    }

    /**
     * @throws ScopeException When no request are available.
     */
    public function createPaginator(string $parameter, int $limit = 25): PaginatorInterface
    {
        if (!$this->container->has(ServerRequestInterface::class)) {
            throw new ScopeException('Unable to create paginator, no request scope found');
        }
        /**
         * @var array $query
         */
        $query = $this->container->get(ServerRequestInterface::class)->getQueryParams();

        //Getting page number
        $page = 0;
        if (!empty($query[$parameter]) && \is_scalar($query[$parameter])) {
            $page = (int)$query[$parameter];
        }

        return $this->factory->make(Paginator::class, ['limit' => $limit, 'parameter' => $parameter])->withPage($page);
    }
}
