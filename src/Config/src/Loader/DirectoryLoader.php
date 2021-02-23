<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Config\Loader;

use Spiral\Config\Exception\LoaderException;
use Spiral\Config\LoaderInterface;

final class DirectoryLoader implements LoaderInterface
{
    /** @var string */
    private $directory;

    /** @var FileLoaderInterface[] */
    private $loaders;

    /**
     * @param string $directory
     * @param array  $loaders
     */
    public function __construct(string $directory, array $loaders = [])
    {
        $this->directory = rtrim($directory, '/');
        $this->loaders = $loaders;
    }

    /**
     * @inheritdoc
     */
    public function has(string $section): bool
    {
        foreach ($this->loaderExtensions() as $extension) {
            $filename = sprintf('%s/%s.%s', $this->directory, $section, $extension);
            if (file_exists($filename)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function load(string $section): array
    {
        foreach ($this->loaderExtensions() as $extension) {
            $filename = sprintf('%s/%s.%s', $this->directory, $section, $extension);
            if (!file_exists($filename)) {
                continue;
            }

            try {
                return $this->getLoader($extension)->loadFile($section, $filename);
            } catch (LoaderException $e) {
                throw new LoaderException("Unable to load config `{$section}`.", $e->getCode(), $e);
            }
        }

        throw new LoaderException("Unable to load config `{$section}`.");
    }

    private function loaderExtensions(): array
    {
        return array_keys($this->loaders);
    }

    /**
     * @param string $extension
     * @return FileLoaderInterface
     */
    private function getLoader(string $extension): FileLoaderInterface
    {
        return $this->loaders[$extension];
    }
}
