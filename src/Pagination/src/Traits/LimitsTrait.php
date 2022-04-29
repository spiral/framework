<?php

declare(strict_types=1);

namespace Spiral\Pagination\Traits;

/**
 * Common functionality with ability to limit selection and specify offsets.
 */
trait LimitsTrait
{
    private int $limit = 0;
    private int $offset = 0;

    /**
     * Set selection limit. Attention, this limit value does not affect values set in paginator but
     * only changes pagination window. Set to 0 to disable limiting.
     */
    public function limit(int $limit = 0): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Set selection offset. Attention, this value does not affect associated paginator but only
     * changes pagination window.
     */
    public function offset(int $offset = 0): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}
