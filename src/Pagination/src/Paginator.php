<?php

declare(strict_types=1);

namespace Spiral\Pagination;

/**
 * Simple predictable paginator.
 *
 * Example usage:
 *
 *     $perPage = 100;
 *     $paginator = new Paginator($perPage, $select->count());
 *     for ($page = 1; $page <= $paginator->countPages(); $page++) {
 *         $paginator
 *             ->withPage($page)
 *             ->paginate($select);
 *         yield $this->toCSVLine($this->toRows($select->fetchData()));
 *     }
 */
final class Paginator implements PaginatorInterface, \Countable
{
    /**
     * Current page number (1-based, clamped to valid range on read).
     *
     * @var positive-int
     */
    private int $pageNumber = 1;

    /**
     * Total number of pages, derived from $count and $limit.
     *
     * @var positive-int
     */
    private int $countPages = 1;

    /**
     * Total number of items across all pages.
     *
     * @var int<0, max>
     */
    private int $count;

    /**
     * @param positive-int $limit Maximum items per page.
     * @param int<0, max> $count Total number of items.
     * @param string|null $parameter Query parameter name used to read the current page from the environment (e.g. "page").
     */
    public function __construct(
        private int $limit = 25,
        int $count = 0,
        private readonly ?string $parameter = null,
    ) {
        $this->limit = \max($this->limit, 1);
        $this->setCount($count);
    }

    /**
     * Get parameter paginator depends on. Environment specific.
     */
    public function getParameter(): ?string
    {
        return $this->parameter;
    }

    /**
     * @param positive-int $limit
     */
    public function withLimit(int $limit): self
    {
        $paginator = clone $this;
        $paginator->limit = \max($limit, 1);
        // $countPages is derived from $count and $limit, so it must be recomputed.
        $paginator->setCount($paginator->count);

        return $paginator;
    }

    /**
     * @return positive-int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    public function withPage(int $number): self
    {
        $paginator = clone $this;
        $paginator->pageNumber = \max($number, 1);

        //Real page number
        return $paginator;
    }

    /**
     * @param int<0, max> $count
     */
    public function withCount(int $count): self
    {
        return (clone $this)->setCount($count);
    }

    /**
     * @return positive-int
     */
    public function getPage(): int
    {
        return match (true) {
            $this->pageNumber > $this->countPages => $this->countPages,
            default => $this->pageNumber,
        };
    }

    /**
     * @return int<0, max>
     */
    public function getOffset(): int
    {
        return ($this->getPage() - 1) * $this->limit;
    }

    public function paginate(PaginableInterface $target): PaginatorInterface
    {
        $paginator = clone $this;
        if ($target instanceof \Countable && $paginator->count === 0) {
            $paginator->setCount($target->count());
        }

        $target->limit($paginator->getLimit());
        $target->offset($paginator->getOffset());

        return $paginator;
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return positive-int
     */
    public function countPages(): int
    {
        return $this->countPages;
    }

    /**
     * @return int<0, max>
     */
    public function countDisplayed(): int
    {
        if ($this->getPage() === $this->countPages) {
            return \max(0, $this->count - $this->getOffset());
        }

        return $this->limit;
    }

    public function isRequired(): bool
    {
        return ($this->countPages > 1);
    }

    /**
     * @return positive-int|null
     */
    public function nextPage(): ?int
    {
        if ($this->getPage() !== $this->countPages) {
            return $this->getPage() + 1;
        }

        return null;
    }

    /**
     * @return positive-int|null
     */
    public function previousPage(): ?int
    {
        $page = $this->getPage();
        if ($page > 1) {
            return $page - 1;
        }

        return null;
    }

    /**
     * Non-Immutable version of withCount.
     *
     * @param int<0, max> $count
     * @return static
     */
    private function setCount(int $count): self
    {
        $this->count = \max($count, 0);
        $this->countPages = $this->count > 0 ? (int) \ceil($this->count / $this->limit) : 1;

        return $this;
    }
}
