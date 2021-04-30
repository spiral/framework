<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Storage\Config\StorageConfig;
use Spiral\Storage\Parser\UriParser;
use Spiral\Storage\Parser\UriParserInterface;
use Spiral\Storage\Storage;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\UriResolver;
use Spiral\Storage\UriResolverInterface;

class StorageBootloader extends Bootloader
{
    /**
     * @var array<class-string, class-string>
     */
    protected const SINGLETONS = [
        UriParser::class          => UriParser::class,
        UriParserInterface::class => UriParser::class,
    ];

    /**
     * @param Container $app
     */
    public function boot(Container $app): void
    {
        $app->bindInjector(StorageConfig::class, ConfiguratorInterface::class);

        $app->bindSingleton(UriResolverInterface::class, $this->uriResolverRegistrar());
        $app->bindSingleton(UriResolver::class, static function (UriResolverInterface $resolver) {
            return $resolver;
        });

        $app->bindSingleton(StorageInterface::class, $this->storageRegistrar());
        $app->bindSingleton(Storage::class, static function (StorageInterface $storage) {
            return $storage;
        });
    }

    /**
     * @return \Closure
     */
    private function storageRegistrar(): \Closure
    {
        return static function (StorageConfig $config, UriParserInterface $parser) {
            return new Storage($config, $parser);
        };
    }

    /**
     * @return \Closure
     */
    private function uriResolverRegistrar(): \Closure
    {
        return static function (StorageConfig $config, UriParserInterface $parser) {
            return new UriResolver($config, $parser);
        };
    }
}
