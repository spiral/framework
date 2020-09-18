<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid;

use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Exception\SchemaException;
use Spiral\DataGrid\GridSchema;
use Spiral\DataGrid\Specification\Filter\Equals;
use Spiral\DataGrid\Specification\Sorter\Sorter;

class GridSchemaTest extends TestCase
{
    public function testAddFilter(): void
    {
        $schema = new GridSchema();
        $schema->addFilter('My Filter', new Equals('', ''));
        $this->assertTrue($schema->hasFilter('My filter'));
        $this->assertTrue($schema->hasFilter('my fIltEr'));
        $this->assertTrue($schema->hasFilter('my filter'));

        $this->assertInstanceOf(Equals::class, $schema->getFilter('my filter'));
        $this->assertArrayHasKey('my filter', $schema->getFilters());
    }

    public function testFilterUniqueness(): void
    {
        $this->expectException(SchemaException::class);
        $schema = new GridSchema();
        $schema->addFilter('my filter', new Equals('', ''));
        $schema->addFilter('my filter', new Equals('', ''));
    }

    public function testFilterNotFound(): void
    {
        $this->expectException(SchemaException::class);
        $schema = new GridSchema();
        $schema->getFilter('my filter');
    }

    public function testAddSorter(): void
    {
        $schema = new GridSchema();
        $schema->addSorter('My Sorter', new Sorter(''));
        $this->assertTrue($schema->hasSorter('My Sorter'));
        $this->assertTrue($schema->hasSorter('my sOrtEr'));
        $this->assertTrue($schema->hasSorter('my sorter'));

        $this->assertInstanceOf(Sorter::class, $schema->getSorter('my sorter'));
        $this->assertArrayHasKey('my sorter', $schema->getSorters());
    }

    public function testSorterUniqueness(): void
    {
        $this->expectException(SchemaException::class);
        $schema = new GridSchema();
        $schema->addSorter('my sorter', new Sorter(''));
        $schema->addSorter('my sorter', new Sorter(''));
    }

    public function testSorterNotFound(): void
    {
        $this->expectException(SchemaException::class);
        $schema = new GridSchema();
        $schema->getSorter('my sorter');
    }
}
