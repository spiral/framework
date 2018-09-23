<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Spiral\Core\Bootloader\Bootloader;
use Spiral\Database\Database;
use Spiral\Database\DatabaseInterface;

class DatabaseBootloader extends Bootloader
{
    const BINDINGS = [
        DatabaseInterface::class => Database::class
    ];
}