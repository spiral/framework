<?php

declare(strict_types=1);

namespace Framework\Bootloader\Http;

use Spiral\Http\PaginationFactory;
use Spiral\Pagination\PaginationProviderInterface;
use Spiral\Tests\Framework\BaseTest;

final class PaginationBootloaderTest extends BaseTest
{
    public function testPaginationProviderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(PaginationProviderInterface::class, PaginationFactory::class);
    }
}
