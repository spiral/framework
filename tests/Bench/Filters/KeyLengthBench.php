<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Filters\Fixtures\TreeFilter;
use Tally\Attribute\Before;
use Tally\Attribute\DataProvider;
use Tally\Attribute\Linear;

/**
 * A tree with one branch and a fixed number of leaves, under a key of growing length in bytes.
 */
final class KeyLengthBench
{
    private const LEAVES = 1000;

    private FilterStand $stand;
    private InputInterface $input;

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function asciiKeys(): iterable
    {
        foreach ([128, 256, 512, 1024] as $length) {
            yield 'key-' . $length => [$length, 'k'];
        }
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function multibyteKeys(): iterable
    {
        foreach ([128, 256, 512, 1024] as $length) {
            yield 'key-' . $length => [$length, 'è'];
        }
    }

    #[DataProvider('asciiKeys')]
    #[Linear('keyLength')]
    public function benchAscii(int $keyLength, string $char): void
    {
        $this->stand->create(TreeFilter::class, $this->input);
    }

    #[DataProvider('multibyteKeys')]
    #[Linear('keyLength')]
    public function benchMultibyte(int $keyLength, string $char): void
    {
        $this->stand->create(TreeFilter::class, $this->input);
    }

    /**
     * @param int $keyLength In bytes.
     */
    #[Before]
    private function prepare(int $keyLength, string $char): void
    {
        $this->stand = new FilterStand();
        $this->stand->warmUp(TreeFilter::class, ['branches' => [['leaves' => [0]]]]);

        $key = \str_repeat($char, \intdiv($keyLength, \strlen($char)));
        $this->input = $this->stand->input(['branches' => [$key => ['leaves' => \array_fill(0, self::LEAVES, 0)]]]);
    }
}
