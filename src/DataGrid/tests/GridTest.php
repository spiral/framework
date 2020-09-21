<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid;

use Exception;
use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Exception\GridViewException;
use Spiral\DataGrid\Grid;

class GridTest extends TestCase
{
    public function testOptions(): void
    {
        $grid = new Grid();
        $this->assertNull($grid->getOption('option'));

        $grid = $grid->withOption('option', 'value');
        $this->assertEquals('value', $grid->getOption('option'));
    }

    public function testSource(): void
    {
        $grid = new Grid();
        $this->assertNull($grid->getSource());

        $grid = $grid->withSource([]);
        $this->assertNotNull($grid->getSource());
    }

    public function testNoIterator(): void
    {
        $this->assertTrue(true);
        $this->expectException(GridViewException::class);

        $grid = new Grid();
        $iterator = $grid->getIterator();
        //Generator will not throw anything until valid method called (or iterated)
        $iterator->valid();
    }

    /**
     * @throws Exception
     */
    public function testView(): void
    {
        $grid = new Grid();
        $grid = $grid->withSource(['a', 'b', 'c', 'hello']);
        $grid = $grid->withView('ucfirst');

        $iterated = [];
        foreach ($grid->getIterator() as $value) {
            $iterated[] = $value;
        }

        $this->assertEquals(['A', 'B', 'C', 'Hello'], $iterated);
    }

    /**
     * @throws Exception
     */
    public function testNoView(): void
    {
        $grid = new Grid();
        $grid = $grid->withSource(['a', 'b', 'c', 'hello']);

        $iterated = [];
        foreach ($grid->getIterator() as $value) {
            $iterated[] = $value;
        }

        $this->assertEquals(['a', 'b', 'c', 'hello'], $iterated);
    }
}
