<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Spiral\Core\Exception\ScopeException;
use Spiral\Framework\ScopeName;
use Spiral\Http\PaginationFactory;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\HttpTestCase;

#[TestScope(ScopeName::Http)]
final class PaginationTest extends HttpTestCase
{
    public function testPaginate(): void
    {
        $this->fakeHttp()->get('/paginate')->assertBodySame('1');
    }

    public function testPaginateError(): void
    {
        $this->expectException(ScopeException::class);

        $this->getContainer()->get(PaginationFactory::class)->createPaginator('page');
    }

    public function testPaginate2(): void
    {
        $this->fakeHttp()->get('/paginate', query: ['page' => 2])->assertBodySame('2');
    }
}
