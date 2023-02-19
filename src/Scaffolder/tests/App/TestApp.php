<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\App;

use Spiral\Boot;
use Spiral\Core\Container;
use Spiral\Scaffolder;
use Throwable;

class TestApp extends Boot\AbstractKernel
{
    protected const LOAD = [
        Scaffolder\Bootloader\ScaffolderBootloader::class
    ];

    /**
     * @param string $target
     * @return mixed|object|null
     * @throws Throwable
     */
    public function get(string $target)
    {
        return $this->container->get($target);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @param string $directory
     * @return string
     * @throws Throwable
     */
    public function directory(string $directory): string
    {
        /** @var Boot\DirectoriesInterface $directories */
        $directories = $this->container->get(Boot\DirectoriesInterface::class);

        return $directories->get($directory);
    }

    /**
     * {@inheritDoc}
     */
    protected function bootstrap(): void
    {
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
            throw new Boot\Exception\BootException('Missing required directory `root`.');
        }

        if (!isset($directories['app'])) {
            $directories['app'] = $directories['root'] . '/';
        }

        return array_merge([
            'vendor'  => $directories['root'] . '/vendor/',
            'runtime' => $directories['root'] . '/runtime/',
            'config'  => $directories['app'] . '/config/',
            'resources' => $directories['root'] . '/resources/',
        ], $directories);
    }
}
