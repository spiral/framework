<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Storage;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Storage\Storage;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\Bucket;
use Spiral\Storage\BucketInterface;
use Spiral\Distribution\DistributionInterface as CdnInterface;

class StorageBootloader extends Bootloader
{
    /**
     * @param Container $app
     * @param ConfiguratorInterface $config
     */
    public function boot(Container $app, ConfiguratorInterface $config): void
    {
        $config->setDefaults(StorageConfig::CONFIG, [
            'default' => Storage::DEFAULT_STORAGE,
            'servers' => [],
            'buckets' => [],
        ]);

        $app->bindInjector(StorageConfig::class, ConfiguratorInterface::class);

        $app->bindSingleton(StorageInterface::class, static function (StorageConfig $config, CdnInterface $cdn) {
            $manager = new Storage($config->getDefaultBucket());

            $distributions = $config->getDistributions();

            foreach ($config->getAdapters() as $name => $adapter) {
                $resolver = isset($distributions[$name])
                    ? $cdn->resolver($distributions[$name])
                    : null;

                $manager->add($name, Bucket::fromAdapter($adapter, $resolver));
            }

            return $manager;
        });

        $app->bindSingleton(Storage::class, static function (StorageInterface $manager) {
            return $manager;
        });

        $app->bindSingleton(BucketInterface::class, static function (StorageInterface $manager) {
            return $manager->bucket();
        });

        $app->bindSingleton(Bucket::class, static function (BucketInterface $storage) {
            return $storage;
        });
    }
}
