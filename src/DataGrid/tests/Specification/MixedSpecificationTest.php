<?php

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Specification;

use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\Specification\Filter\Equals;
use Spiral\DataGrid\Specification\Filter\SortedFilter;
use Spiral\DataGrid\Specification\Sorter\DescSorter;
use Spiral\DataGrid\Specification\Sorter\FilteredSorter;
use Spiral\Tests\DataGrid\Fixture\SequenceWriter;
use Spiral\Tests\Files\TestCase;

class MixedSpecificationTest extends TestCase
{
    public function testSortedFilter(): void
    {
        $sf = new SortedFilter('sorted', new Equals('field', 10), new DescSorter('field'));

        $compiler = new Compiler();
        $compiler->addWriter(new SequenceWriter());
        $compiler->compile([], $sf);

        $this->assertSame([Equals::class, DescSorter::class], $compiler->compile([], $sf));
    }

    public function testFilteredSorter(): void
    {
        $fs = new FilteredSorter('sorted', new Equals('field', 10), new DescSorter('field'));
        $compiler = new Compiler();
        $compiler->addWriter(new SequenceWriter());

        $this->assertSame([Equals::class, DescSorter::class], $compiler->compile([], $fs));
    }
}
