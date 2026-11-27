<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Bench\Filters\Fixture\WrapListFilter;
use Tally\Attribute\Before;
use Tally\Attribute\DataProvider;
use Tally\Attribute\Linear;

/**
 * A {@see \Spiral\Filters\Attribute\NestedFilter} inside an element of a
 * {@see \Spiral\Filters\Attribute\NestedArray} with a key of the longest allowed length, holding a nested
 * array of leaves: the filter built by the attribute mapper must not be built again by the input mapper.
 */
final class NestedFilterInArrayBench
{
    private const KEY_LENGTH = 1024;

    private FilterStand $stand;
    private InputInterface $input;

    /**
     * @return iterable<string, array{int}>
     */
    public static function sizes(): iterable
    {
        yield 'leaves-1250' => [1250];
        yield 'leaves-2500' => [2500];
        yield 'leaves-5000' => [5000];
    }

    #[DataProvider('sizes')]
    #[Linear('leaves')]
    public function benchLeaves(int $leaves): void
    {
        $this->stand->create(WrapListFilter::class, $this->input);
    }

    #[Before]
    private function prepare(int $leaves): void
    {
        $this->stand = new FilterStand();
        $this->stand->warmUp(WrapListFilter::class, ['wraps' => [['flat' => ['items' => [0]]]]]);

        $key = \str_repeat('k', self::KEY_LENGTH);
        $this->input = $this->stand->input(['wraps' => [$key => ['flat' => ['items' => \array_fill(0, $leaves, 0)]]]]);
    }
}
