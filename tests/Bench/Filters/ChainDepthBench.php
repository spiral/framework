<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Bench\Filters\Fixture\WideNodeFilter;
use Tally\Attribute\Before;
use Tally\Attribute\DataProvider;
use Tally\Attribute\Linear;

/**
 * A chain of self-referencing filters of growing depth with a fixed wide level at the bottom: every
 * filter at the bottom reads its fields under a prefix as long as the chain.
 *
 * The deepest chain stays within what a JSON body can carry under the default depth of json_decode()
 * (512, two levels per link).
 */
final class ChainDepthBench
{
    private const FAN = 200;

    private FilterStand $stand;
    private InputInterface $input;

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function shortKeys(): iterable
    {
        yield 'depth-60' => [60, 1];
        yield 'depth-120' => [120, 1];
        yield 'depth-240' => [240, 1];
    }

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function longKeys(): iterable
    {
        yield 'depth-60' => [60, 1024];
        yield 'depth-120' => [120, 1024];
        yield 'depth-240' => [240, 1024];
    }

    #[DataProvider('shortKeys')]
    #[Linear('depth')]
    public function benchShortKeys(int $depth, int $keyLength): void
    {
        $this->stand->create(WideNodeFilter::class, $this->input);
    }

    #[DataProvider('longKeys')]
    #[Linear('depth')]
    public function benchLongKeys(int $depth, int $keyLength): void
    {
        $this->stand->create(WideNodeFilter::class, $this->input);
    }

    #[Before]
    private function prepare(int $depth, int $keyLength): void
    {
        $this->stand = new FilterStand();
        $this->stand->warmUp(WideNodeFilter::class, ['children' => [['children' => []]]]);

        $key = \str_repeat('k', $keyLength);
        $node = ['children' => \array_fill(0, self::FAN, ['children' => []])];
        for ($i = 0; $i < $depth; ++$i) {
            $node = ['children' => [$key => $node]];
        }

        $this->input = $this->stand->input($node);
    }
}
