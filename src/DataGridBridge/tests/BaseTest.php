<?php

/**
 * Spiral Framework. Data Grid Bridge.
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid;

use PHPUnit\Framework\TestCase;
use Spiral\Database\Database;
use Spiral\Database\Driver\SQLite\SQLiteDriver;
use Spiral\Database\Query\SelectQuery;
use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\SpecificationInterface;
use Spiral\DataGrid\Writer\QueryWriter;

abstract class BaseTest extends TestCase
{
    protected function compile($source, SpecificationInterface ...$specifications)
    {
        $compiler = new Compiler();
        $compiler->addWriter(new QueryWriter());

        return $compiler->compile($source, ...$specifications);
    }

    /**
     * @return SelectQuery
     */
    protected function initQuery(): SelectQuery
    {
        return (new Database('default', '', new SQLiteDriver([])))->select()->from('users');
    }

    /**
     * @param string      $expected
     * @param SelectQuery $compiled
     */
    protected function assertEqualSQL(string $expected, SelectQuery $compiled): void
    {
        $this->assertSame(
            preg_replace("/\s+/", '', $expected),
            preg_replace("/\s+/", '', (string)$compiled)
        );
    }
}
