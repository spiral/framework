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
use Spiral\Core\Exception\InvalidArgumentException;
use Spiral\Storage\Manager;
use Spiral\Storage\ManagerInterface;
use Spiral\Storage\Storage;
use Spiral\Storage\StorageInterface;
use Spiral\Distribution\ManagerInterface as CdnInterface;

class StorageBootloader extends Bootloader
{
    /**
     * @param Container $app
     * @param ConfiguratorInterface $config
     */
    public function boot(Container $app, ConfiguratorInterface $config): void
    {
        $app->bindInjector(StorageConfig::class, ConfiguratorInterface::class);

        $app->bindSingleton(ManagerInterface::class, static function (StorageConfig $config, CdnInterface $cdn) {
            $manager = new Manager($config->getDefaultBucket());

            $distributions = $config->getDistributions();

            foreach ($config->getAdapters() as $name => $adapter) {
                $resolver = isset($distributions[$name])
                    ? $cdn->resolver($distributions[$name])
                    : null;

                $manager->add($name, Storage::fromAdapter($adapter, $resolver));
            }

            return $manager;
        });

        $app->bindSingleton(Manager::class, static function (ManagerInterface $manager) {
            return $manager;
        });

        $app->bindSingleton(StorageInterface::class, static function (ManagerInterface $manager) {
            return $manager->storage();
        });

        $app->bindSingleton(Storage::class, static function (StorageInterface $storage) {
            return $storage;
        });
    }
}
