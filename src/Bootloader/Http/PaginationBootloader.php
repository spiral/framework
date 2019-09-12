<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Http\PaginationFactory;
use Spiral\Pagination\PaginationProviderInterface;

final class PaginationBootloader extends Bootloader implements DependedInterface
{
    const SINGLETONS = [
        PaginationProviderInterface::class => PaginationFactory::class
    ];

    /**
     * @return array
     */
    public function defineDependencies(): array
    {
        return [
            HttpBootloader::class
        ];
    }
}
