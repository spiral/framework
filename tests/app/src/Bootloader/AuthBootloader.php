<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\App\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\BootloadManager;
use Spiral\Bootloader\Auth\TokenStorage\SessionTokensBootloader;

class AuthBootloader extends Bootloader
{
    public function boot(BootloadManager $bootloadManager): void
    {
        $bootloadManager->bootload([SessionTokensBootloader::class]);
    }
}
