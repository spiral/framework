<?php

declare(strict_types=1);

namespace Spiral\Pagination;

/**
 * Simple predictable paginator.
 */
final class Paginator implements PaginatorInterface, \Countable
{
    private int $pageNumber = 1;
    private int $countPages = 1;
    private int $count;

    public function __construct(
        private int $limit = 25,
        int $count = 0,
        private readonly ?string $parameter = null,
    ) {
        $this->setCount($count);
    }

    /**
     * Get parameter paginator depends on. Environment specific.
     */
    public function getParameter(): ?string
    {
        return $this->parameter;
    }

    public function withLimit(int $limit): self
    {
        $paginator = clone $this;
        $paginator->limit = $limit;

        return $paginator;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function withPage(int $number): self
    {
        $paginator = clone $this;
        $paginator->pageNumber = \max($number, 0);

        //Real page number
        return $paginator;
    }

    public function withCount(int $count): self
    {
        return (clone $this)->setCount($count);
    }

    public function getPage(): int
    {
        return match (true) {
            $this->pageNumber < 1 => 1,
            $this->pageNumber > $this->countPages => $this->countPages,
            default => $this->pageNumber
        };
    }

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

    public function count(): int
    {
        return $this->count;
    }

    public function countPages(): int
    {
        return $this->countPages;
    }

    public function countDisplayed(): int
    {
        if ($this->getPage() === $this->countPages) {
            return $this->count - $this->getOffset();
        }

        return $this->limit;
    }

    public function isRequired(): bool
    {
        return ($this->countPages > 1);
    }

    public function nextPage(): ?int
    {
        if ($this->getPage() !== $this->countPages) {
            return $this->getPage() + 1;
        }

        return null;
    }

    public function previousPage(): ?int
    {
        if ($this->getPage() > 1) {
            return $this->getPage() - 1;
        }

        return null;
    }

    /**
     * Non-Immutable version of withCount.
     */
    private function setCount(int $count): self
    {
        $this->count = \max($count, 0);
        $this->countPages = $this->count > 0 ? (int)\ceil($this->count / $this->limit) : 1;

        return $this;
    }
}
