<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Core\Core;
use Spiral\Core\CoreInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;

final class RouterBootloader extends Bootloader implements DependedInterface
{
    const SINGLETONS = [
        CoreInterface::class           => Core::class,
        RouterInterface::class         => [self::class, 'router'],
        RequestHandlerInterface::class => RouterInterface::class,
    ];

    /**
     * @return array
     */
    public function defineDependencies(): array
    {
        return [HttpBootloader::class];
    }

    /**
     * @param HttpConfig         $config
     * @param ContainerInterface $container
     * @return RouterInterface
     */
    protected function router(HttpConfig $config, ContainerInterface $container): RouterInterface
    {
        return new Router($config->basePath(), $container);
    }
}