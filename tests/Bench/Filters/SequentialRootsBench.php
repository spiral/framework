<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Bench\Filters\Fixture\FlatFilter;
use Tally\Attribute\Before;
use Tally\Attribute\Constant;
use Tally\Attribute\DataProvider;
use Tally\Meter;

/**
 * Root filters built one after another in one process, as a long-running worker does, each with as
 * many nested filters as the limit allows: only the last one is counted, and its cost must not depend
 * on how many came before. A count carried over from an earlier root would fail it on the limit.
 */
final class SequentialRootsBench
{
    private const LIMIT = 1000;

    private FilterStand $stand;
    private InputInterface $input;

    public function __construct(
        private readonly Meter $meter,
    ) {}

    /**
     * @return iterable<string, array{int}>
     */
    public static function sizes(): iterable
    {
        yield 'roots-1' => [1];
        yield 'roots-4' => [4];
        yield 'roots-16' => [16];
    }

    #[DataProvider('sizes')]
    #[Constant('roots')]
    public function benchLastRoot(int $roots): void
    {
        $this->meter->pause();
        for ($i = 1; $i < $roots; ++$i) {
            $this->stand->create(FlatFilter::class, $this->input);
        }
        // Otherwise the garbage of the earlier roots may be collected during the counted one.
        \gc_collect_cycles();
        $this->meter->resume();

        $this->stand->create(FlatFilter::class, $this->input);
    }

    #[Before]
    private function prepare(): void
    {
        $this->stand = new FilterStand(['maxNestedFilters' => self::LIMIT]);
        $this->stand->warmUp(FlatFilter::class, ['items' => [0]]);
        $this->input = $this->stand->input(['items' => \array_fill(0, self::LIMIT, 0)]);
    }
}
