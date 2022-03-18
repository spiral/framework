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
use Spiral\Boot\Exception\EnvironmentException;
use Spiral\Bootloader\Distribution\DistributionBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Storage\BucketFactory;
use Spiral\Storage\BucketFactoryInterface;
use Spiral\Storage\Storage;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\Bucket;
use Spiral\Storage\BucketInterface;
use Spiral\Distribution\DistributionInterface as CdnInterface;

class StorageBootloader extends Bootloader
{
    /** @var array<string, class-string|callable> */
    protected const SINGLETONS = [
        BucketFactoryInterface::class => BucketFactory::class,
    ];

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

        $app->bindSingleton(StorageInterface::class, static function (
            BucketFactoryInterface $bucketFactory,
            StorageConfig $config,
            Container $app
        ) {
            $manager = new Storage($config->getDefaultBucket());

            $distributions = $config->getDistributions();

            foreach ($config->getAdapters() as $name => $adapter) {
                $resolver = null;

                if (isset($distributions[$name])) {
                    try {
                        $cdn = $app->make(CdnInterface::class);
                    } catch (NotFoundException $e) {
                        $message = 'Unable to create distribution for bucket "%s". '
                            . 'Please make sure that bootloader %s is added in your application';
                        $message = \sprintf($message, $name, DistributionBootloader::class);

                        throw new EnvironmentException($message, (int)$e->getCode(), $e);
                    }

                    $resolver = $cdn->resolver($distributions[$name]);
                }

                $manager->add($name, $bucketFactory->createFromAdapter($adapter, $name, $resolver));
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
