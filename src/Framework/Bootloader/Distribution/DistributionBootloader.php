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
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Distribution\Manager;
use Spiral\Distribution\DistributionInterface;
use Spiral\Distribution\MutableDistributionInterface;
use Spiral\Distribution\Resolver\UriResolver;
use Spiral\Distribution\UriResolverInterface;

class DistributionBootloader extends Bootloader
{
    /**
     * @param ConfiguratorInterface $config
     * @param Container $app
     */
    public function boot(ConfiguratorInterface $config, Container $app): void
    {
        $config->setDefaults(DistributionConfig::CONFIG, [
            'default' => Manager::DEFAULT_RESOLVER,
            'resolvers' => []
        ]);

        $app->bindInjector(DistributionConfig::class, ConfiguratorInterface::class);

        $this->registerManager($app);
        $this->registerResolver($app);
    }

    /**
     * @param Container $app
     */
    private function registerResolver(Container $app): void
    {
        $app->bindSingleton(UriResolverInterface::class, static function (DistributionInterface $manager) {
            return $manager->resolver();
        });

        $app->bindSingleton(UriResolver::class, static function (Container $app) {
            return $app->get(UriResolverInterface::class);
        });
    }

    /**
     * @param Container $app
     */
    private function registerManager(Container $app): void
    {
        $app->bindSingleton(DistributionInterface::class, static function (DistributionConfig $config) {
            $manager = new Manager($config->getDefaultDriver());

            foreach ($config->getResolvers() as $name => $resolver) {
                $manager->add($name, $resolver);
            }

            return $manager;
        });

        $app->bindSingleton(MutableDistributionInterface::class, static function (Container $app) {
            return $app->get(DistributionInterface::class);
        });

        $app->bindSingleton(Manager::class, static function (Container $app) {
            return $app->get(DistributionInterface::class);
        });
    }
}
