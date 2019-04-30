<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Core;

use Psr\Container\ContainerInterface;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\Exception\BootException;
use Spiral\Bootloader\System\TokenizerBootloader;

abstract class Kernel extends AbstractKernel
{
    const SYSTEM = [CoreBootloader::class, TokenizerBootloader::class];
    const APP    = [];

    /**
     * Each application can define it's own boot sequence.
     */
    protected function bootstrap()
    {
        $this->bootloader->bootload(static::APP);
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return !empty($this->getEnvironment()->get('DEBUG'));
    }

    /**
     * @return EnvironmentInterface
     */
    public function getEnvironment(): EnvironmentInterface
    {
        return $this->container->get(EnvironmentInterface::class);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Normalizes directory list and adds all required aliases.
     *
     * @param array $directories
     * @return array
     */
    protected function mapDirectories(array $directories): array
    {
        if (!isset($directories['root'])) {
            throw new BootException("Missing required directory `root`.");
        }

        if (!isset($directories['app'])) {
            $directories['app'] = $directories['root'] . '/app/';
        }

        return array_merge([
            // public root
            'public'    => $directories['root'] . '/public/',

            // vendor libraries
            'vendor'    => $directories['root'] . '/vendor/',

            // data directories
            'runtime'   => $directories['root'] . '/runtime/',
            'cache'     => $directories['root'] . '/runtime/cache/',

            // application directories
            'config'    => $directories['app'] . '/config/',
            'resources' => $directories['app'] . '/resources/',
        ], $directories);
    }
}