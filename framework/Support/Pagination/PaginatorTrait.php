<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Pagination;

use Psr\Http\Message\ServerRequestInterface;

trait PaginatorTrait
{
    /**
     * Current limit value.
     *
     * @var int
     */
    protected $limit = 0;

    /**
     * Current offset value.
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * Paginator associated with selection.
     *
     * @var Paginator
     */
    protected $paginator = null;

    /**
     * Forced pagination count. If 0 PaginatorTrait will try to fetch value from associated object
     * (this).
     *
     * @var int
     */
    protected $paginationCount = 0;

    /**
     * Count elements of an object.
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int
     */
    abstract public function count();

    /**
     * Get current limit value.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set selection limit.
     *
     * @param int $limit
     * @return static
     */
    public function limit($limit = 0)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Get current offset value.
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set selection offset.
     *
     * @param int $offset
     * @return static
     */
    public function offset($offset = 0)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Paginate current selection. If count parameter provided with null value, pagination will fetch
     * count from target object (this may cause additional query).
     *
     * @param int                    $limit         Pagination limit.
     * @param int|null               $count         Forced count value, if null paginator will try to
     *                                              fetch count from associated object.
     * @param string                 $pageParameter Name of parameter in request query which is used
     *                                              to store the current page number. "page" by default.
     * @param ServerRequestInterface $request       Source of page number.
     * @return static
     */
    public function paginate(
        $limit = 25,
        $count = null,
        $pageParameter = 'page',
        ServerRequestInterface $request = null
    )
    {

        $arguments = compact('pageParameter');

        if (!empty($request))
        {
            $arguments['request'] = $request;
        }

        $this->paginator = Paginator::make($arguments);
        $this->paginator->setLimit($limit);
        $this->paginationCount = $count;

        return $this;
    }

    /**
     * Get paginator for the current selection. Paginate method should be already called.
     *
     * @return Paginator
     * @throws PaginatorException
     */
    public function getPaginator()
    {
        if (!$this->paginator)
        {
            throw new PaginatorException("Selection has to be paginated before requesting Paginator.");
        }

        return $this->paginator;
    }

    /**
     * Apply pagination to current object. Will be applied only if internal paginator already constructed.
     *
     * @return static
     */
    protected function doPagination()
    {
        if (empty($this->paginator))
        {
            return $this;
        }

        if (!empty($this->paginationCount))
        {
            $this->paginator->setCount($this->paginationCount);
        }

        return $this->paginator->paginateObject($this, empty($this->paginationCount));
    }
}