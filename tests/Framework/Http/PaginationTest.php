<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Spiral\Core\Exception\ScopeException;
use Spiral\Http\PaginationFactory;
use Spiral\Tests\Framework\HttpTestCase;

final class PaginationTest extends HttpTestCase
{
    public function testPaginate(): void
    {
        $this->get('/paginate')->assertBodySame('1');
    }

    public function testPaginateError(): void
    {
        $this->expectException(ScopeException::class);

        $this->getContainer()->get(PaginationFactory::class)->createPaginator('page');
    }

    public function testPaginate2(): void
    {
        $this->get('/paginate', query: ['page' => 2])->assertBodySame('2');
    }
}
