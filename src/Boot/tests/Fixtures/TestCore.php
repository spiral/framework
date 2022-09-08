<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\Exception\BootException;
use Spiral\Framework\Kernel;

class TestCore extends Kernel
{
    protected const SYSTEM = [
        CoreBootloader::class
    ];

    protected const LOAD = [
        ConfigBootloader::class,
    ];

    public function getContainer()
    {
        return $this->container;
    }

    protected function bootstrap(): void
    {
        parent::bootstrap();

        $this->container->get(EnvironmentInterface::class)->set('INTERNAL', 'VALUE');
    }

    /**
     * Normalizes directory list and adds all required alises.
     *
     * @param array $directories
     * @return array
     */
    protected function mapDirectories(array $directories): array
    {
        if (!isset($directories['root'])) {
            throw new BootException('Missing required directory `root`.');
        }

        if (!isset($directories['app'])) {
            $directories['app'] = $directories['root'] . '/app/';
        }

        return array_merge([
            // public root
            'public'    => $directories['root'] . '/public/',

            // data directories
            'runtime'   => $directories['root'] . '/runtime/',
            'cache'     => $directories['root'] . '/runtime/cache/',

            // application directories
            'config'    => $directories['app'] . '/config/',
            'resources' => $directories['app'] . '/resources/',
        ], $directories);
    }
}
