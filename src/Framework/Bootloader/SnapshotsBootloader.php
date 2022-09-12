<?php

declare(strict_types=1);

namespace Spiral\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Exceptions\Renderer\PlainRenderer;
use Spiral\Exceptions\Verbosity;
use Spiral\Files\FilesInterface;
use Spiral\Snapshots\FileSnapshooter;
use Spiral\Snapshots\FileSnapshot;
use Spiral\Snapshots\SnapshotterInterface;

/**
 * Depends on environment variables:
 * SNAPSHOT_MAX_FILES: defaults to 25
 * SNAPSHOT_VERBOSITY: defaults to {@see \Spiral\Exceptions\Verbosity::VERBOSE} (1)
 */
final class SnapshotsBootloader extends Bootloader
{
    protected const SINGLETONS = [
        FileSnapshot::class => [self::class, 'fileSnapshot'],
        SnapshotterInterface::class => FileSnapshooter::class,
    ];

    private const MAX_SNAPSHOTS = 25;

    /**
     * @noRector RemoveUnusedPrivateMethodRector
     */
    private function fileSnapshot(
        EnvironmentInterface $env,
        DirectoriesInterface $dirs,
        FilesInterface $files
    ): FileSnapshot {
        return new FileSnapshot(
            $dirs->get('runtime') . '/snapshots/',
            (int) $env->get('SNAPSHOT_MAX_FILES', self::MAX_SNAPSHOTS),
            Verbosity::tryFrom((int)($env->get('SNAPSHOT_VERBOSITY') ?? Verbosity::VERBOSE->value)),
            new PlainRenderer(),
            $files
        );
    }
}
