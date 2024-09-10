<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\App;

use Spiral\Boot\AbstractKernel;
use Spiral\Scaffolder\Bootloader\ScaffolderBootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\Exception\BootException;
use Spiral\Boot;
use Spiral\Core\Container;
use Spiral\Scaffolder;
use Spiral\Validation\Bootloader\ValidationBootloader;
use Throwable;

class TestApp extends AbstractKernel
{
    protected const LOAD = [
        ScaffolderBootloader::class,
        ValidationBootloader::class,
    ];

    /**
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
     * @throws Throwable
     */
    public function directory(string $directory): string
    {
        /** @var Boot\DirectoriesInterface $directories */
        $directories = $this->container->get(DirectoriesInterface::class);

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
     */
    protected function mapDirectories(array $directories): array
    {
        if (!isset($directories['root'])) {
            throw new BootException('Missing required directory `root`.');
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
