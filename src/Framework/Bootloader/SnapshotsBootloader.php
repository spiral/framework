<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader;

use Psr\Log\LoggerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Exceptions\HandlerInterface;
use Spiral\Exceptions\PlainHandler;
use Spiral\Files\FilesInterface;
use Spiral\Snapshots\FileSnapshooter;
use Spiral\Snapshots\SnapshotterInterface;

/**
 * Depends on environment variables:
 * SNAPSHOT_MAX_FILES: defaults to 25
 * SNAPSHOT_VERBOSITY: defaults to HandlerInterface::VERBOSITY_VERBOSE (1)
 */
final class SnapshotsBootloader extends Bootloader
{
    protected const SINGLETONS = [
        SnapshotterInterface::class => [self::class, 'fileSnapshooter'],
    ];

    private const MAX_SNAPSHOTS = 25;

    /**
     * @param EnvironmentInterface $env
     * @param DirectoriesInterface $dirs
     * @param FilesInterface       $files
     * @param LoggerInterface|null $logger
     * @return FileSnapshooter
     */
    private function fileSnapshooter(
        EnvironmentInterface $env,
        DirectoriesInterface $dirs,
        FilesInterface $files,
        LoggerInterface $logger = null
    ) {
        return new FileSnapshooter(
            $dirs->get('runtime') . '/snapshots/',
            (int) $env->get('SNAPSHOT_MAX_FILES', self::MAX_SNAPSHOTS),
            (int) $env->get('SNAPSHOT_VERBOSITY', HandlerInterface::VERBOSITY_VERBOSE),
            new PlainHandler(),
            $files,
            $logger
        );
    }
}
