<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Spiral\Core\Bootloader\Bootloader;
use Spiral\Pagination\PaginationFactory;
use Spiral\Pagination\PaginatorsInterface;

class PaginationBootloader extends Bootloader
{
    const SINGLETONS = [
        PaginatorsInterface::class => PaginationFactory::class
    ];
}