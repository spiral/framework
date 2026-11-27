<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Filters\Fixtures\TreeFilter;
use Tally\Attribute\Before;
use Tally\Attribute\DataProvider;
use Tally\Attribute\Linear;

/**
 * A tree of many branches, each with its own key of the longest allowed length and a pair of leaves.
 */
final class LongKeyBranchesBench
{
    private const KEY_LENGTH = 1024;

    private FilterStand $stand;
    private InputInterface $input;

    /**
     * @return iterable<string, array{int}>
     */
    public static function sizes(): iterable
    {
        yield 'branches-800' => [800];
        yield 'branches-1600' => [1600];
        yield 'branches-3200' => [3200];
    }

    #[DataProvider('sizes')]
    #[Linear('branches')]
    public function benchBranches(int $branches): void
    {
        $this->stand->create(TreeFilter::class, $this->input);
    }

    #[Before]
    private function prepare(int $branches): void
    {
        $this->stand = new FilterStand();
        $this->stand->warmUp(TreeFilter::class, ['branches' => [['leaves' => [0]]]]);

        $body = [];
        for ($i = 0; $i < $branches; ++$i) {
            $body[\str_pad((string) $i, self::KEY_LENGTH, 'k')] = ['leaves' => [0, 0]];
        }

        $this->input = $this->stand->input(['branches' => $body]);
    }
}
