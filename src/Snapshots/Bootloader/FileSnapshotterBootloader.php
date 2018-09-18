<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Snapshots\Bootloader;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\Bootloader\Bootloader;
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
class FileSnapshotterBootloader extends Bootloader
{
    const DEFAULT_MAX_SNAPSHOTS = 25;

    const SINGLETONS = [
        SnapshotterInterface::class => [self::class, 'exceptionSnapshotter']
    ];

    /**
     * @param DirectoriesInterface $directories
     * @param EnvironmentInterface $environment
     * @param FilesInterface       $files
     * @return FileSnapshotter
     */
    protected function exceptionSnapshotter(
        DirectoriesInterface $directories,
        EnvironmentInterface $environment,
        FilesInterface $files
    ) {
        return new FileSnapshotter(
            $directories->get('runtime') . '/snapshots/',
            $environment->get('SNAPSHOT_MAX_FILES', self::DEFAULT_MAX_SNAPSHOTS),
            $environment->get('SNAPSHOT_VERBOSITY', HandlerInterface::VERBOSITY_VERBOSE),
            new HtmlHandler(),
            $files
        );
    }
}