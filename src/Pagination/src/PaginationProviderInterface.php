<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Pagination;

/**
 * Responsible for paginator creation based on a given pagination parameter (parameter in this case
 * is an abstract definition which might depend on specific implementation if needed).
 */
interface PaginationProviderInterface
{
    /**
     * Create paginator for a given parameter, scope request must be resolved automatically.
     *
     * @param string $parameter
     * @param int    $limit Pagination limit
     *
     * @return PaginatorInterface
     */
    public function createPaginator(string $parameter, int $limit = 25): PaginatorInterface;
}
