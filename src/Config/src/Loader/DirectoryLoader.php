<?php

declare(strict_types=1);

namespace Spiral\Config\Loader;

use Spiral\Config\Exception\LoaderException;
use Spiral\Config\LoaderInterface;

final class DirectoryLoader implements LoaderInterface
{
    private readonly string $directory;

    /**
     * @param FileLoaderInterface[] $loaders
     */
    public function __construct(
        string $directory,
        private readonly array $loaders = []
    ) {
        $this->directory = \rtrim($directory, '/');
    }

    public function has(string $section): bool
    {
        foreach ($this->loaderExtensions() as $extension) {
            $filename = \sprintf('%s/%s.%s', $this->directory, $section, $extension);
            if (\file_exists($filename)) {
                return true;
            }
        }

        return false;
    }

    public function load(string $section): array
    {
        foreach ($this->loaderExtensions() as $extension) {
            $filename = \sprintf('%s/%s.%s', $this->directory, $section, $extension);
            if (!\file_exists($filename)) {
                continue;
            }

            try {
                return $this->getLoader($extension)->loadFile($section, $filename);
            } catch (LoaderException $e) {
                throw new LoaderException("Unable to load config `{$section}`: {$e->getMessage()}", $e->getCode(), $e);
            }
        }

        throw new LoaderException(\sprintf('Unable to load config `%s`: no suitable loader found.', $section));
    }

    private function loaderExtensions(): array
    {
        return \array_keys($this->loaders);
    }

    private function getLoader(string $extension): FileLoaderInterface
    {
        return $this->loaders[$extension];
    }
}
