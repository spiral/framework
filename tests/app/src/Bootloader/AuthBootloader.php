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
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\Auth\TokenStorage\CycleTokensBootloader;
use Spiral\Bootloader\Auth\TokenStorage\SessionTokensBootloader;

class AuthBootloader extends Bootloader
{
    public function boot(
        EnvironmentInterface $env,
        BootloadManager $bootloadManager
    ): void {
        if ($env->get('CYCLE_AUTH')) {
            $bootloadManager->bootload([CycleTokensBootloader::class]);
            return;
        }

        $bootloadManager->bootload([SessionTokensBootloader::class]);
    }
}
