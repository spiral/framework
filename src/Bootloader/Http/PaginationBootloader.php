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
use Spiral\Http\PaginationFactory;
use Spiral\Pagination\PaginationProviderInterface;

final class PaginationBootloader extends Bootloader
{
    public const DEPENDENCIES = [
        HttpBootloader::class
    ];

    public const SINGLETONS = [
        PaginationProviderInterface::class => PaginationFactory::class
    ];
}
