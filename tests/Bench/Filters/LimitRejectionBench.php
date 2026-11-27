<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\Exception\NestedInputLimitException;
use Spiral\Filters\InputInterface;
use Spiral\Tests\Bench\Filters\Fixture\FlatFilter;
use Tally\Attribute\Before;
use Tally\Attribute\Constant;
use Tally\Attribute\DataProvider;

/**
 * A {@see \Spiral\Filters\Attribute\NestedArray} far over the limit of nested filters is rejected before
 * any of its elements is built.
 */
final class LimitRejectionBench
{
    private FilterStand $stand;
    private InputInterface $input;

    /**
     * @return iterable<string, array{int}>
     */
    public static function sizes(): iterable
    {
        yield 'items-200k' => [200_000];
        yield 'items-400k' => [400_000];
    }

    #[DataProvider('sizes')]
    #[Constant('items')]
    public function benchRejected(int $items): void
    {
        try {
            $this->stand->create(FlatFilter::class, $this->input);
        } catch (NestedInputLimitException) {
            return;
        }

        // Tally reports a case that throws as not measured, which fails the run.
        throw new \LogicException('The input was expected to exceed the limit of nested filters.');
    }

    #[Before]
    private function prepare(int $items): void
    {
        // The default limits, as an application gets them.
        $this->stand = new FilterStand([]);
        $this->stand->warmUp(FlatFilter::class, ['items' => [0]]);
        $this->input = $this->stand->input(['items' => \array_fill(0, $items, 0)]);
    }
}
