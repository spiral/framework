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

        self::assertInstanceOf(PaginatorInterface::class, $paginator);
    }

    public function testParameterTracking(): void
    {
        $paginator = new Paginator(limit: 25, count: 0, parameter: 'request:page');
        self::assertSame('request:page', $paginator->getParameter());
    }

    public function testLimit(): void
    {
        $paginator = new Paginator(limit: 25);

        self::assertSame(25, $paginator->getLimit());
        $newPaginator = $paginator->withLimit(50);
        self::assertSame(25, $paginator->getLimit());
        self::assertSame(50, $newPaginator->getLimit());
    }

    public function testLimitWithCounts(): void
    {
        $paginator = new Paginator(limit: 25, count: 100);

        self::assertCount(100, $paginator);
        self::assertSame(4, $paginator->countPages());
    }

    public function testCountsAndPages(): void
    {
        $paginator = new Paginator(limit: 25);

        self::assertCount(0, $paginator);
        self::assertCount($paginator->count(), $paginator);
        self::assertCount($paginator->count(), $paginator);

        self::assertSame(1, $paginator->getPage());
        self::assertSame(0, $paginator->getOffset());
        self::assertSame(1, $paginator->countPages());
        self::assertSame(0, $paginator->countDisplayed());
    }

    public function testFirstPage(): void
    {
        $paginator = new Paginator(limit: 25);
        $paginator = $paginator->withCount(100);

        self::assertSame(1, $paginator->getPage());

        self::assertNull($paginator->previousPage());
        self::assertSame(2, $paginator->nextPage());

        self::assertCount(100, $paginator);
        self::assertCount($paginator->count(), $paginator);
        self::assertCount($paginator->count(), $paginator);

        self::assertSame(0, $paginator->getOffset());
        self::assertSame(4, $paginator->countPages());
        self::assertSame(25, $paginator->countDisplayed());
    }

    public function testSecondPage(): void
    {
        $paginator = new Paginator(limit: 25);
        $paginator = $paginator->withCount(110);

        self::assertCount(110, $paginator);
        self::assertCount($paginator->count(), $paginator);
        self::assertCount($paginator->count(), $paginator);

        self::assertSame(1, $paginator->getPage());

        $paginator = $paginator->withPage(2);

        self::assertSame(1, $paginator->previousPage());
        self::assertSame(3, $paginator->nextPage());

        self::assertSame(2, $paginator->getPage());
        self::assertSame(25, $paginator->getOffset());
        self::assertSame(5, $paginator->countPages());
        self::assertSame(25, $paginator->countDisplayed());
    }

    public function testLastPage(): void
    {
        $paginator = new Paginator(limit: 25);
        $paginator = $paginator->withCount(100);

        self::assertSame(1, $paginator->getPage());

        self::assertNull($paginator->previousPage());
        self::assertSame(2, $paginator->nextPage());

        $paginator = $paginator->withPage(100);

        self::assertSame(4, $paginator->getPage());
        self::assertSame(3, $paginator->previousPage());
        self::assertNull($paginator->nextPage());

        self::assertCount(100, $paginator);
        self::assertCount($paginator->count(), $paginator);
        self::assertCount($paginator->count(), $paginator);

        self::assertSame(75, $paginator->getOffset());
        self::assertSame(4, $paginator->countPages());
        self::assertSame(25, $paginator->countDisplayed());
    }

    public function testNegativePage(): void
    {
        $paginator = new Paginator(limit: 25);
        $paginator = $paginator->withCount(100);
        $paginator = $paginator->withPage(-1);

        self::assertSame(1, $paginator->getPage());

        self::assertCount(100, $paginator);
        self::assertCount($paginator->count(), $paginator);
        self::assertCount($paginator->count(), $paginator);
    }

    public function testNegativeCount(): void
    {
        $paginator = new Paginator(limit: 25);
        $paginator = $paginator->withCount(-100);

        $paginator = $paginator->withPage(-10);
        self::assertSame(1, $paginator->getPage());

        self::assertNull($paginator->previousPage());
        self::assertNull($paginator->nextPage());

        self::assertCount(0, $paginator);
        self::assertSame(0, $paginator->getOffset());
        self::assertSame(1, $paginator->countPages());
        self::assertSame(0, $paginator->countDisplayed());
    }

    public function testLastPageNumber(): void
    {
        $paginator = new Paginator(limit: 25);
        $paginator = $paginator->withCount(110);

        self::assertCount(110, $paginator);
        self::assertSame(1, $paginator->getPage());

        $paginator = $paginator->withPage(100);

        self::assertSame($paginator->countPages(), $paginator->getPage());
        self::assertSame(($paginator->getPage() - 1) * $paginator->getLimit(), $paginator->getOffset());

        self::assertSame(5, $paginator->countPages());
        self::assertSame(10, $paginator->countDisplayed());
    }

    public function testIsRequired(): void
    {
        $paginator = new Paginator(limit: 25);

        $paginator = $paginator->withCount(24);
        self::assertFalse($paginator->isRequired());

        $paginator = $paginator->withCount(25);
        self::assertFalse($paginator->isRequired());

        $paginator = $paginator->withCount(26);
        self::assertTrue($paginator->isRequired());
    }
}
