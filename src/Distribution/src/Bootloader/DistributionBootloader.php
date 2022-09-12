<?php

declare(strict_types=1);

namespace Spiral\Distribution\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\BinderInterface;
use Spiral\Distribution\Config\DistributionConfig;
use Spiral\Distribution\DistributionInterface;
use Spiral\Distribution\Manager;
use Spiral\Distribution\MutableDistributionInterface;
use Spiral\Distribution\Resolver\UriResolver;
use Spiral\Distribution\UriResolverInterface;

class DistributionBootloader extends Bootloader
{
    public function init(ConfiguratorInterface $config, BinderInterface $binder): void
    {
        $config->setDefaults(DistributionConfig::CONFIG, [
            'default' => Manager::DEFAULT_RESOLVER,
            'resolvers' => [],
        ]);

        $this->registerManager($binder);
        $this->registerResolver($binder);
    }

    private function registerResolver(BinderInterface $binder): void
    {
        $binder->bindSingleton(
            UriResolverInterface::class,
            static fn (DistributionInterface $dist) => $dist->resolver()
        );

        $binder->bindSingleton(
            UriResolver::class,
            static fn (ContainerInterface $app) => $app->get(UriResolverInterface::class)
        );
    }

    private function registerManager(BinderInterface $binder): void
    {
        $binder->bindSingleton(DistributionInterface::class, static function (DistributionConfig $config) {
            $manager = new Manager($config->getDefaultDriver());

            foreach ($config->getResolvers() as $name => $resolver) {
                $manager->add($name, $resolver);
            }

            return $manager;
        });

        $binder->bindSingleton(
            MutableDistributionInterface::class,
            static fn (ContainerInterface $app) => $app->get(DistributionInterface::class)
        );

        $binder->bindSingleton(
            Manager::class,
            static fn (ContainerInterface $app) => $app->get(DistributionInterface::class)
        );
    }
}
