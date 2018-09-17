<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Snapshots\Bootloaders;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Exceptions\HandlerInterface;
use Spiral\Exceptions\HtmlHandler;
use Spiral\Files\FilesInterface;
use Spiral\Snapshots\ExceptionSnapshots;
use Spiral\Snapshots\SnapshotterInterface;

/**
 * Depends on environment variables:
 * SNAPSHOT_MAX_FILES: defaults to 25
 * SNAPSHOT_VERBOSITY: defaults to HandlerInterface::VERBOSITY_VERBOSE (1)
 */
class SnapshotBootloader extends Bootloader
{
    const DEFAULT_MAX_SNAPSHOTS = 25;

    const SINGLETONS = [
        SnapshotterInterface::class => [self::class, 'errorSnapshotter']
    ];

    /**
     * @param DirectoriesInterface $directories
     * @param EnvironmentInterface $environment
     * @param FilesInterface       $files
     * @return ExceptionSnapshots
     */
    protected function errorSnapshotter(
        DirectoriesInterface $directories,
        EnvironmentInterface $environment,
        FilesInterface $files
    ) {
        return new ExceptionSnapshots(
            $directories->get('runtime') . '/snapshots/',
            $environment->get('SNAPSHOT_MAX_FILES', self::DEFAULT_MAX_SNAPSHOTS),
            $environment->get('SNAPSHOT_VERBOSITY', HandlerInterface::VERBOSITY_VERBOSE),
            new HtmlHandler(),
            $files
        );
    }
}