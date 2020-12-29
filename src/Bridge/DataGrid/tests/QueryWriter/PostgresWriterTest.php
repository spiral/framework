<?php

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\QueryWriter;

use Spiral\Database\Database;
use Spiral\Database\Driver\Postgres\PostgresDriver;
use Spiral\Database\Query\SelectQuery;
use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\Specification\Filter;
use Spiral\DataGrid\SpecificationInterface;
use Spiral\DataGrid\Writer\PostgresQueryWriter;
use Spiral\DataGrid\Writer\QueryWriter;
use Spiral\Tests\DataGrid\BaseTest;

class PostgresWriterTest extends BaseTest
{
    public function testIlike(): void
    {
        $select = $this->compile(
            $this->initQuery(),
            new Filter\Any(
                new Filter\Like('name', 'Antony'),
                new Filter\Postgres\ILike('email', '@example.')
            )
        );

        $this->assertEqualSQL(
            'SELECT * FROM "users" WHERE (("name" LIKE \'%Antony%\') OR ("email" ILIKE \'%@example.%\'))',
            $select
        );
    }

    /**
     * @return SelectQuery
     */
    protected function initQuery(): SelectQuery
    {
        return (new Database('default', '', new PostgresDriver([])))->select()->from('users');
    }

    protected function compile($source, SpecificationInterface ...$specifications)
    {
        $compiler = new Compiler();
        $compiler->addWriter(new QueryWriter());
        $compiler->addWriter(new PostgresQueryWriter());

        return $compiler->compile($source, ...$specifications);
    }
}
