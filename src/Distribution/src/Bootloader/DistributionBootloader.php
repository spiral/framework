<?php

declare(strict_types=1);

namespace Spiral\Distribution\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Distribution\Config\DistributionConfig;
use Spiral\Distribution\DistributionInterface;
use Spiral\Distribution\Manager;
use Spiral\Distribution\MutableDistributionInterface;
use Spiral\Distribution\Resolver\UriResolver;
use Spiral\Distribution\UriResolverInterface;

class DistributionBootloader extends Bootloader
{
    public function init(ConfiguratorInterface $config, Container $app): void
    {
        $config->setDefaults(DistributionConfig::CONFIG, [
            'default' => Manager::DEFAULT_RESOLVER,
            'resolvers' => [],
        ]);

        $this->registerManager($app);
        $this->registerResolver($app);
    }

    private function registerResolver(Container $app): void
    {
        $app->bindSingleton(UriResolverInterface::class, static fn (DistributionInterface $dist) => $dist->resolver());
        $app->bindSingleton(UriResolver::class, static fn (Container $app) => $app->get(UriResolverInterface::class));
    }

    private function registerManager(Container $app): void
    {
        $app->bindSingleton(DistributionInterface::class, static function (DistributionConfig $config) {
            $manager = new Manager($config->getDefaultDriver());

            foreach ($config->getResolvers() as $name => $resolver) {
                $manager->add($name, $resolver);
            }

            return $manager;
        });

        $app->bindSingleton(
            MutableDistributionInterface::class,
            static fn (Container $app) => $app->get(DistributionInterface::class)
        );

        $app->bindSingleton(Manager::class, static fn (Container $app) => $app->get(DistributionInterface::class));
    }
}
