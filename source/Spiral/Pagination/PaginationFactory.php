<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Pagination;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Exceptions\ScopeException;

/**
 * Paginators factory binded to active request scope in order to select page number.
 */
class PaginationFactory implements SingletonInterface, PaginatorsInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ScopeException When no request are available.
     */
    public function createPaginator(string $parameter, int $limit = 25): PaginatorInterface
    {
        if (!$this->container->has(ServerRequestInterface::class)) {
            throw new ScopeException("Unable to create paginator, no request scope found");
        }

        /**
         * @var array $query
         */
        $query = $this->container->get(ServerRequestInterface::class)->getQueryParams();

        //Getting page number
        $page = 0;
        if (!empty($query[$parameter]) && is_scalar($query[$parameter])) {
            $page = (int)$query[$parameter];
        }

        //Initiating paginator
        return $this->container->make(Paginator::class, compact('limit'))->withPage($page);
    }
}