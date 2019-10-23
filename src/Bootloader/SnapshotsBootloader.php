<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Exceptions\HandlerInterface;
use Spiral\Exceptions\HtmlHandler;
use Spiral\Files\FilesInterface;
use Spiral\Snapshots\FileSnapshotter;
use Spiral\Snapshots\SnapshotterInterface;

/**
 * Depends on environment variables:
 * SNAPSHOT_MAX_FILES: defaults to 25
 * SNAPSHOT_VERBOSITY: defaults to HandlerInterface::VERBOSITY_VERBOSE (1)
 */
final class SnapshotsBootloader extends Bootloader
{
    protected const SINGLETONS = [
        SnapshotterInterface::class => [self::class, 'fileSnapshotter']
    ];

    private const MAX_SNAPSHOTS = 25;

    /**
     * @param EnvironmentInterface $env
     * @param DirectoriesInterface $dirs
     * @param FilesInterface       $files
     * @return FileSnapshotter
     */
    protected function fileSnapshotter(
        EnvironmentInterface $env,
        DirectoriesInterface $dirs,
        FilesInterface $files
    ) {
        return new FileSnapshotter(
            $dirs->get('runtime') . '/snapshots/',
            $env->get('SNAPSHOT_MAX_FILES', self::MAX_SNAPSHOTS),
            $env->get('SNAPSHOT_VERBOSITY', HandlerInterface::VERBOSITY_VERBOSE),
            new HtmlHandler(),
            $files
        );
    }
}