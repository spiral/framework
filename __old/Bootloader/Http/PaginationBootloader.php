<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\Http;

use Spiral\Core\Bootloader\Bootloader;
use Spiral\Http\PaginationFactory;
use Spiral\Pagination\PaginatorsInterface;

class PaginationBootloader extends Bootloader
{
    const SINGLETONS = [
        PaginatorsInterface::class => PaginationFactory::class
    ];
}