<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Bench\Filters\Fixture\FlatFilter;
use Tally\Attribute\Before;
use Tally\Attribute\Constant;
use Tally\Attribute\DataProvider;

/**
 * A flat {@see \Spiral\Filters\Attribute\NestedArray} next to many root keys no filter reads, each
 * holding an array of its own: building the nested filters must neither walk nor copy them.
 */
final class JunkArrayRootKeysBench
{
    private const ITEMS = 5000;

    private FilterStand $stand;
    private InputInterface $input;

    /**
     * @return iterable<string, array{int}>
     */
    public static function sizes(): iterable
    {
        yield 'junk-100k' => [100_000];
        yield 'junk-200k' => [200_000];
    }

    #[DataProvider('sizes')]
    #[Constant('junk')]
    public function benchJunk(int $junk): void
    {
        $this->stand->create(FlatFilter::class, $this->input);
    }

    #[Before]
    private function prepare(int $junk): void
    {
        $this->stand = new FilterStand();
        $this->stand->warmUp(FlatFilter::class, ['items' => [0]]);

        $body = ['items' => \array_fill(0, self::ITEMS, 0)];
        for ($i = 0; $i < $junk; ++$i) {
            $body['k' . $i] = ['items' => [$i, ['name' => 'n' . $i]]];
        }

        $this->input = $this->stand->input($body);
    }
}
