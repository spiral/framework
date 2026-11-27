<?php

declare(strict_types=1);

namespace Spiral\Filters\Model;

use Spiral\Core\Attribute\Singleton;
use Spiral\Filters\Config\FiltersConfig;
use Spiral\Filters\Exception\NestedInputLimitException;

/**
 * Applies the nested input limits of {@see FiltersConfig} while a root filter is being built.
 *
 * @internal
 */
#[Singleton]
final class NestedInputLimits
{
    /** @var positive-int|null */
    private readonly ?int $maxFilters;

    /** @var positive-int|null */
    private readonly ?int $maxKeyLength;

    /** Nesting level of the filter being built; 0 while no filter is being built. */
    private int $depth = 0;

    /** Nested filters built so far for the current root filter. */
    private int $filters = 0;

    public function __construct(FiltersConfig $config = new FiltersConfig())
    {
        $this->maxFilters = $config->getMaxNestedFilters();
        $this->maxKeyLength = $config->getMaxNestedKeyLength();
    }

    /**
     * Marks the start of a filter build. Every call must be paired with {@see leaveFilter()},
     * unless it throws.
     *
     * @throws NestedInputLimitException
     */
    public function enterFilter(): void
    {
        // Restarting with every root filter keeps the counter from carrying over to the next
        // request of a long-running worker.
        if ($this->depth === 0) {
            $this->filters = 0;
        } elseif ($this->maxFilters !== null && ++$this->filters > $this->maxFilters) {
            throw new NestedInputLimitException(
                \sprintf('The number of nested filters exceeds the limit of %d.', $this->maxFilters),
            );
        }

        ++$this->depth;
    }

    public function leaveFilter(): void
    {
        --$this->depth;
    }

    /**
     * Checks a key of a nested array element before it becomes part of an input prefix.
     *
     * @throws NestedInputLimitException
     */
    public function assertKey(string $field, int|string $key): void
    {
        if ($this->maxKeyLength !== null && \strlen((string) $key) > $this->maxKeyLength) {
            throw (new NestedInputLimitException(
                \sprintf('The key of a nested filter exceeds the limit of %d bytes.', $this->maxKeyLength),
            ))->under($field);
        }
    }
}
