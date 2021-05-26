<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Distribution;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Storage\StorageConfig;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Distribution\Manager;
use Spiral\Distribution\ManagerInterface;
use Spiral\Distribution\MutableManagerInterface;
use Spiral\Distribution\Resolver\Resolver;
use Spiral\Distribution\ResolverInterface;

class DistributionBootloader extends Bootloader
{
    /**
     * @param ConfiguratorInterface $config
     * @param Container $app
     */
    public function boot(ConfiguratorInterface $config, Container $app): void
    {
        $config->setDefaults(StorageConfig::CONFIG, [
            'default' => Manager::DEFAULT_RESOLVER,
            'servers' => [],
            'buckets' => [],
        ]);
        $this->registerConfig($config, $app);

        $this->registerManager($app);
        $this->registerResolver($app);
    }

    /**
     * @param Container $app
     */
    private function registerConfig(Container $app): void
    {
        $app->bindInjector(DistributionConfig::class, ConfiguratorInterface::class);
    }

    /**
     * @param Container $app
     */
    private function registerResolver(Container $app): void
    {
        $app->bindSingleton(ResolverInterface::class, static function (ManagerInterface $manager) {
            return $manager->resolver();
        });

        $app->bindSingleton(Resolver::class, static function (Container $app) {
            return $app->get(ResolverInterface::class);
        });
    }

    /**
     * @param Container $app
     */
    private function registerManager(Container $app): void
    {
        $app->bindSingleton(ManagerInterface::class, static function (DistributionConfig $config) {
            $manager = new Manager($config->getDefaultDriver());

            foreach ($config->getResolvers() as $name => $resolver) {
                $manager->add($name, $resolver);
            }

            return $manager;
        });

        $app->bindSingleton(MutableManagerInterface::class, static function (Container $app) {
            return $app->get(ManagerInterface::class);
        });

        $app->bindSingleton(Manager::class, static function (Container $app) {
            return $app->get(ManagerInterface::class);
        });
    }
}
