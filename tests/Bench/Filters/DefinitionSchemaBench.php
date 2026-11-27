<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Bench\Filters\Fixture\DefinitionListFilter;
use Tally\Attribute\Before;
use Tally\Attribute\DataProvider;
use Tally\Attribute\Linear;

/**
 * A nested array declared as `[Class, 'items.*']` in the mapping schema of a
 * {@see \Spiral\Filters\Model\HasFilterDefinition}, which only the input mapper builds.
 */
final class DefinitionSchemaBench
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
        $this->stand->create(DefinitionListFilter::class, $this->input);
    }

    #[Before]
    private function prepare(int $items): void
    {
        $this->stand = new FilterStand();
        $this->stand->warmUp(DefinitionListFilter::class, ['items' => [0]]);
        $this->input = $this->stand->input(['items' => \array_fill(0, $items, 0)]);
    }
}
