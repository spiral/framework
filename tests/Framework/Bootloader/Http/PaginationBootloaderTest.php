<?php

declare(strict_types=1);

namespace Framework\Bootloader\Http;

use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Spiral\Framework\Spiral;
use Spiral\Http\PaginationFactory;
use Spiral\Pagination\PaginationProviderInterface;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\BaseTestCase;

final class PaginationBootloaderTest extends BaseTestCase
{
    #[TestScope(Spiral::HttpRequest)]
    public function testPaginationProviderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(PaginationProviderInterface::class, PaginationFactory::class);
    }

    #[WithoutErrorHandler]
    public function testPaginationProviderInterfaceBindingInRootScope(): void
    {
        \set_error_handler(static function (int $errno, string $error): void {
            self::assertSame(\sprintf(
                'Using `%s` outside of the `http.request` scope is deprecated and will be impossible in version 4.0.',
                PaginationProviderInterface::class
            ), $error);
        });

        $this->assertContainerBoundAsSingleton(PaginationProviderInterface::class, PaginationProviderInterface::class);

        \restore_error_handler();
    }
}
