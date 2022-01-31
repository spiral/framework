<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader;

use Composer\InstalledVersions;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\BootloadManager;
use Spiral\Bootloader\Server\LegacyRoadRunnerBootloader;
use Spiral\Bootloader\Server\RoadRunnerBootloader;
use Spiral\RoadRunner\Http\HttpWorker;

/**
 * Configures RPC connection to upper RoadRunner server.
 *
 * @deprecated since 3.0
 */
final class ServerBootloader extends Bootloader
{
    /**
     * @param BootloadManager $manager
     * @throws \Throwable
     */
    public function boot(BootloadManager $manager): void
    {
        $bootloader = $this->isLegacy()
            ? LegacyRoadRunnerBootloader::class
            : RoadRunnerBootloader::class
        ;

        $manager->bootload([$bootloader]);
    }

    /**
     * @return bool
     */
    private function isLegacy(): bool
    {
        if (\class_exists(InstalledVersions::class)) {
            $version = InstalledVersions::getVersion('spiral/roadrunner');

            return \str_starts_with($version, '1.');
        }

        return !\class_exists(HttpWorker::class);
    }
}
