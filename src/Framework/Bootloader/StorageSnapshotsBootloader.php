<?php

declare(strict_types=1);

namespace Spiral\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Snapshots\SnapshotterInterface;
use Spiral\Snapshots\StorageSnapshooter;
use Spiral\Snapshots\StorageSnapshot;
use Spiral\Storage\Bootloader\StorageBootloader;

/**
 * Depends on environment variables:
 * SNAPSHOTS_BUCKET: bucket name
 * SNAPSHOTS_DIRECTORY: where snapshots will be stored in the bucket
 * SNAPSHOT_VERBOSITY: defaults to {@see \Spiral\Exceptions\Verbosity::VERBOSE} (1)
 */
final class StorageSnapshotsBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        StorageBootloader::class,
    ];

    protected const SINGLETONS = [
        StorageSnapshot::class => [self::class, 'storageSnapshot'],
        SnapshotterInterface::class => StorageSnapshooter::class,
    ];

    private function storageSnapshot(EnvironmentInterface $env, FactoryInterface $factory): StorageSnapshot
    {
        $bucket = $env->get('SNAPSHOTS_BUCKET');

        if ($bucket === null) {
            throw new \RuntimeException(
                'Please, configure a bucket for storing snapshots using the environment variable `SNAPSHOTS_BUCKET`.'
            );
        }

        return $factory->make(StorageSnapshot::class, [
            'bucket' => $bucket,
            'directory' => $env->get('SNAPSHOTS_DIRECTORY', null),
        ]);
    }
}
