<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;
use Spiral\Core\Config\DeprecationProxy;
use Spiral\Framework\Spiral;
use Spiral\Http\PaginationFactory;
use Spiral\Pagination\PaginationProviderInterface;

final class PaginationBootloader extends Bootloader
{
    public function __construct(
        private readonly BinderInterface $binder,
    ) {
    }

    public function defineDependencies(): array
    {
        return [
            HttpBootloader::class,
        ];
    }

    public function defineSingletons(): array
    {
        $httpRequest = $this->binder->getBinder(Spiral::HttpRequest);
        $httpRequest->bindSingleton(PaginationFactory::class, PaginationFactory::class);
        $httpRequest->bindSingleton(PaginationProviderInterface::class, PaginationFactory::class);

        $this->binder->bind(
            PaginationProviderInterface::class,
            new DeprecationProxy(PaginationProviderInterface::class, true, Spiral::HttpRequest, '4.0')
        );

        return [];
    }
}
