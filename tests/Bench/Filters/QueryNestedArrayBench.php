<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Bench\Filters\Fixture\QueryListFilter;
use Tally\Attribute\Before;
use Tally\Attribute\DataProvider;
use Tally\Attribute\Linear;

/**
 * A flat {@see \Spiral\Filters\Attribute\NestedArray} read from the query string instead of the body.
 */
final class QueryNestedArrayBench
{
    private FilterStand $stand;
    private InputInterface $input;

    /**
     * @return iterable<string, array{int}>
     */
    public static function sizes(): iterable
    {
        yield 'items-1250' => [1250];
        yield 'items-2500' => [2500];
        yield 'items-5000' => [5000];
    }

    #[DataProvider('sizes')]
    #[Linear('items')]
    public function benchItems(int $items): void
    {
        $this->stand->create(QueryListFilter::class, $this->input);
    }

    #[Before]
    private function prepare(int $items): void
    {
        $this->stand = new FilterStand();
        $this->stand->warmUp(QueryListFilter::class, [], ['items' => [['name' => 'n']]]);
        $this->input = $this->stand->input([], ['items' => \array_fill(0, $items, ['name' => 'n'])]);
    }
}
