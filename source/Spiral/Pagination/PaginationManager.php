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
class PaginationManager implements SingletonInterface, PaginatorsInterface
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
         * @var ServerRequestInterface $request
         */
        $request = $this->container->get(ServerRequestInterface::class);

        //Getting page number
        $page = 0;
        if (!empty($request->getQueryParams()[$parameter]) && is_scalar($request->getQueryParams()[$parameter])) {
            $page = (int)$request->getQueryParams()[$parameter];
        }

        //Initiating paginator
        return $this->container->make(Paginator::class, compact('limit'))->withPage($page);
    }
}