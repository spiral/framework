<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\InputInterface;
use Spiral\Tests\Bench\Filters\Fixture\RejectingListFilter;
use Tally\Attribute\Before;
use Tally\Attribute\DataProvider;
use Tally\Attribute\Linear;

/**
 * A flat {@see \Spiral\Filters\Attribute\NestedArray} whose every element fails on a setter, so the
 * response carries an error under the path of each element.
 */
final class LeafErrorsBench
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
    public function benchErrors(int $items): void
    {
        try {
            $this->stand->create(RejectingListFilter::class, $this->input);
        } catch (ValidationException $e) {
            if (\count($e->errors['items'] ?? []) === $items) {
                return;
            }
        }

        // Tally reports a case that throws as not measured, which fails the run.
        throw new \LogicException('Every element was expected to fail validation.');
    }

    #[Before]
    private function prepare(int $items): void
    {
        $this->stand = new FilterStand(validate: true);
        $this->stand->warmUp(RejectingListFilter::class, ['items' => [['name' => 'n']]]);
        $this->input = $this->stand->input(['items' => \array_fill(0, $items, ['name' => 'n'])]);
    }
}
