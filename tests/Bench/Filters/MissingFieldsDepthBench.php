<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Bench\Filters\Fixture\WideNodeFilter;
use Tally\Attribute\Before;
use Tally\Attribute\DataProvider;
use Tally\Attribute\Linear;

/**
 * A chain of self-referencing filters whose ten fields are all missing from the input. Looking up a
 * missing field must cost the same at any depth: an exception for it would carry a backtrace of the
 * whole nested filter stack.
 */
final class MissingFieldsDepthBench
{
    private FilterStand $stand;
    private InputInterface $input;

    /**
     * @return iterable<string, array{int}>
     */
    public static function sizes(): iterable
    {
        yield 'depth-60' => [60];
        yield 'depth-120' => [120];
        yield 'depth-240' => [240];
    }

    #[DataProvider('sizes')]
    #[Linear('depth')]
    public function benchDepth(int $depth): void
    {
        $this->stand->create(WideNodeFilter::class, $this->input);
    }

    #[Before]
    private function prepare(int $depth): void
    {
        $this->stand = new FilterStand();
        $this->stand->warmUp(WideNodeFilter::class, ['children' => [['children' => []]]]);

        $node = ['children' => []];
        for ($i = 0; $i < $depth; ++$i) {
            $node = ['children' => [$node]];
        }

        $this->input = $this->stand->input($node);
    }
}
