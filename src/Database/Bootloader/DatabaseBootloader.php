<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Database\Bootloader;

use Spiral\Core\Bootloader\Bootloader;
use Spiral\Database\Database;
use Spiral\Database\DatabaseInterface;
use Spiral\Database\DBAL;

class DatabaseBootloader extends Bootloader
{
    const SINGLETONS = [
        DBAL::class => DBAL::class
    ];

    const BINDINGS = [
        DatabaseInterface::class => Database::class
    ];
}