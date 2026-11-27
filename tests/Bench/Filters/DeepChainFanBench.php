<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Bench\Filters\Fixture\WideNodeFilter;
use Tally\Attribute\Before;
use Tally\Attribute\DataProvider;
use Tally\Attribute\Linear;

/**
 * A deep chain of self-referencing filters with long keys and a wide level at the bottom, together just
 * under the default limit of nested filters: every filter at the bottom gets the longest input prefix
 * the chain can make.
 */
final class DeepChainFanBench
{
    private const DEPTH = 250;
    private const KEY_LENGTH = 1024;

    private FilterStand $stand;
    private InputInterface $input;

    /**
     * @return iterable<string, array{int}>
     */
    public static function sizes(): iterable
    {
        yield 'fan-1200' => [1200];
        yield 'fan-2400' => [2400];
    }

    #[DataProvider('sizes')]
    #[Linear('fan')]
    public function benchFan(int $fan): void
    {
        $this->stand->create(WideNodeFilter::class, $this->input);
    }

    #[Before]
    private function prepare(int $fan): void
    {
        $this->stand = new FilterStand();
        $this->stand->warmUp(WideNodeFilter::class, ['children' => [['children' => []]]]);

        $key = \str_repeat('k', self::KEY_LENGTH);
        $node = ['children' => \array_fill(0, $fan, ['children' => []])];
        for ($i = 0; $i < self::DEPTH; ++$i) {
            $node = ['children' => [$key => $node]];
        }

        $this->input = $this->stand->input($node);
    }
}
