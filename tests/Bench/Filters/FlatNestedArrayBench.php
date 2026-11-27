<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Bench\Filters\Fixture\FlatFilter;
use Tally\Attribute\Before;
use Tally\Attribute\DataProvider;
use Tally\Attribute\Linear;

/**
 * A flat {@see \Spiral\Filters\Attribute\NestedArray} close to the default limit of nested filters.
 */
final class FlatNestedArrayBench
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
        $this->stand->create(FlatFilter::class, $this->input);
    }

    #[Before]
    private function prepare(int $items): void
    {
        $this->stand = new FilterStand();
        $this->stand->warmUp(FlatFilter::class, ['items' => [0]]);
        $this->input = $this->stand->input(['items' => \array_fill(0, $items, 0)]);
    }
}
