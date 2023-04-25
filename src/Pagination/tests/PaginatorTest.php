<?php

declare(strict_types=1);

namespace Spiral\Tests\Pagination;

use PHPUnit\Framework\TestCase;
use Spiral\Pagination\Paginator;
use Spiral\Pagination\PaginatorInterface;

class PaginatorTest extends TestCase
{
    public function testInterfaces(): void
    {
        $paginator = new Paginator(limit: 25);

        $this->assertInstanceOf(PaginatorInterface::class, $paginator);
    }

    public function testParameterTracking(): void
    {
        $paginator = new Paginator(limit: 25, count: 0, parameter: 'request:page');
        $this->assertSame('request:page', $paginator->getParameter());
    }

    public function testLimit(): void
    {
        $paginator = new Paginator(limit: 25);

        $this->assertSame(25, $paginator->getLimit());
        $newPaginator = $paginator->withLimit(50);
        $this->assertSame(25, $paginator->getLimit());
        $this->assertSame(50, $newPaginator->getLimit());
    }

    public function testLimitWithCounts(): void
    {
        $paginator = new Paginator(limit: 25, count: 100);

        $this->assertSame(100, $paginator->count());
        $this->assertSame(4, $paginator->countPages());
    }

    public function testCountsAndPages(): void
    {
        $paginator = new Paginator(limit: 25);

        $this->assertSame(0, $paginator->count());
        $this->assertSame($paginator->count(), $paginator->count());
        $this->assertSame($paginator->count(), count($paginator));

        $this->assertSame(1, $paginator->getPage());
        $this->assertSame(0, $paginator->getOffset());
        $this->assertSame(1, $paginator->countPages());
        $this->assertSame(0, $paginator->countDisplayed());
    }

    public function testFirstPage(): void
    {
        $paginator = new Paginator(limit: 25);
        $paginator = $paginator->withCount(100);

        $this->assertSame(1, $paginator->getPage());

        $this->assertSame(null, $paginator->previousPage());
        $this->assertSame(2, $paginator->nextPage());

        $this->assertSame(100, $paginator->count());
        $this->assertSame($paginator->count(), $paginator->count());
        $this->assertSame($paginator->count(), count($paginator));

        $this->assertSame(0, $paginator->getOffset());
        $this->assertSame(4, $paginator->countPages());
        $this->assertSame(25, $paginator->countDisplayed());
    }

    public function testSecondPage(): void
    {
        $paginator = new Paginator(limit: 25);
        $paginator = $paginator->withCount(110);

        $this->assertSame(110, $paginator->count());
        $this->assertSame($paginator->count(), $paginator->count());
        $this->assertSame($paginator->count(), count($paginator));

        $this->assertSame(1, $paginator->getPage());

        $paginator = $paginator->withPage(2);

        $this->assertSame(1, $paginator->previousPage());
        $this->assertSame(3, $paginator->nextPage());

        $this->assertSame(2, $paginator->getPage());
        $this->assertSame(25, $paginator->getOffset());
        $this->assertSame(5, $paginator->countPages());
        $this->assertSame(25, $paginator->countDisplayed());
    }

    public function testLastPage(): void
    {
        $paginator = new Paginator(limit: 25);
        $paginator = $paginator->withCount(100);

        $this->assertSame(1, $paginator->getPage());

        $this->assertSame(null, $paginator->previousPage());
        $this->assertSame(2, $paginator->nextPage());

        $paginator = $paginator->withPage(100);

        $this->assertSame(4, $paginator->getPage());
        $this->assertSame(3, $paginator->previousPage());
        $this->assertSame(null, $paginator->nextPage());

        $this->assertSame(100, $paginator->count());
        $this->assertSame($paginator->count(), $paginator->count());
        $this->assertSame($paginator->count(), count($paginator));

        $this->assertSame(75, $paginator->getOffset());
        $this->assertSame(4, $paginator->countPages());
        $this->assertSame(25, $paginator->countDisplayed());
    }

    public function testNegativePage(): void
    {
        $paginator = new Paginator(limit: 25);
        $paginator = $paginator->withCount(100);
        $paginator = $paginator->withPage(-1);

        $this->assertSame(1, $paginator->getPage());

        $this->assertSame(100, $paginator->count());
        $this->assertSame($paginator->count(), $paginator->count());
        $this->assertSame($paginator->count(), count($paginator));
    }

    public function testNegativeCount(): void
    {
        $paginator = new Paginator(limit: 25);
        $paginator = $paginator->withCount(-100);

        $paginator = $paginator->withPage(-10);
        $this->assertSame(1, $paginator->getPage());

        $this->assertSame(null, $paginator->previousPage());
        $this->assertSame(null, $paginator->nextPage());

        $this->assertSame(0, $paginator->count());
        $this->assertSame(0, $paginator->getOffset());
        $this->assertSame(1, $paginator->countPages());
        $this->assertSame(0, $paginator->countDisplayed());
    }

    public function testLastPageNumber(): void
    {
        $paginator = new Paginator(limit: 25);
        $paginator = $paginator->withCount(110);

        $this->assertSame(110, $paginator->count());
        $this->assertSame(1, $paginator->getPage());

        $paginator = $paginator->withPage(100);

        $this->assertSame($paginator->countPages(), $paginator->getPage());
        $this->assertSame(
            ($paginator->getPage() - 1) * $paginator->getLimit(),
            $paginator->getOffset()
        );

        $this->assertSame(5, $paginator->countPages());
        $this->assertSame(10, $paginator->countDisplayed());
    }

    public function testIsRequired(): void
    {
        $paginator = new Paginator(limit: 25);

        $paginator = $paginator->withCount(24);
        $this->assertFalse($paginator->isRequired());

        $paginator = $paginator->withCount(25);
        $this->assertFalse($paginator->isRequired());

        $paginator = $paginator->withCount(26);
        $this->assertTrue($paginator->isRequired());
    }
}
