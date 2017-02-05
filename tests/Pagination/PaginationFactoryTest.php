<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Pagination;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Pagination\PaginatorInterface;
use Spiral\Tests\BaseTest;
use Zend\Diactoros\ServerRequest;

class PaginationFactoryTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Core\Exceptions\ScopeException
     */
    public function testBadScope()
    {
        $paginator = $this->paginators->createPaginator('page');
    }

    public function testGoodScope()
    {
        $scope = $this->container->replace(
            ServerRequestInterface::class,
            new ServerRequest([], [], null, null, 'php://input', [], [], ['page' => 1])
        );

        $paginator = $this->paginators->createPaginator('page', 25);
        $this->assertInstanceOf(PaginatorInterface::class, $paginator);

        $paginator = $paginator->withCount(100);
        $this->assertSame(1, $paginator->getPage());
        $this->assertSame(25, $paginator->getLimit());
        $this->assertSame(0, $paginator->getOffset());

        $this->container->restore($scope);
    }

    public function testGoodScopeBadParameter()
    {
        $scope = $this->container->replace(
            ServerRequestInterface::class,
            new ServerRequest([], [], null, null, 'php://input', [], [], ['page' => ['a']])
        );

        $paginator = $this->paginators->createPaginator('page', 25);
        $this->assertInstanceOf(PaginatorInterface::class, $paginator);

        $paginator = $paginator->withCount(100);
        $this->assertSame(1, $paginator->getPage());
        $this->assertSame(25, $paginator->getLimit());
        $this->assertSame(0, $paginator->getOffset());

        $this->app->container->restore($scope);
    }

    public function testGoodScopeSecondPage()
    {
        $scope = $this->container->replace(
            ServerRequestInterface::class,
            new ServerRequest([], [], null, null, 'php://input', [], [], ['page' => 2])
        );

        /**@var \Spiral\Pagination\CountingInterface $paginator */
        $paginator = $this->paginators->createPaginator('page', 25);
        $this->assertInstanceOf(PaginatorInterface::class, $paginator);

        $paginator = $paginator->withCount(100);
        $this->assertSame(2, $paginator->getPage());
        $this->assertSame(25, $paginator->getLimit());
        $this->assertSame(25, $paginator->getOffset());

        $this->container->restore($scope);
    }
}