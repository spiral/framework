<?php

declare(strict_types=1);

namespace Spiral\Config\Loader;

use Spiral\Config\Exception\LoaderException;
use Spiral\Config\LoaderInterface;

/**
 * @internal
 *
 * Load configuration from the first found file in the provided directories.
 */
final class SingleFileStrategyLoader implements LoaderInterface
{
    public function __construct(
        private readonly DirectoriesRepositoryInterface $directories,
        private readonly FileLoaderRegistry $fileLoader,
    ) {}

    public function has(string $section): bool
    {
        foreach ($this->fileLoader->getExtensions() as $extension) {
            if ($this->findFile($section, $extension) !== null) {
                return true;
            }
        }

        return false;
    }

    public function load(string $section): array
    {
        foreach ($this->fileLoader->getExtensions() as $extension) {
            $filename = $this->findFile($section, $extension);
            if ($filename === null) {
                continue;
            }

            try {
                return $this->fileLoader->getLoader($extension)->loadFile($section, $filename);
            } catch (LoaderException $e) {
                throw new LoaderException("Unable to load config `{$section}`: {$e->getMessage()}", $e->getCode(), $e);
            }
        }

        throw new LoaderException(\sprintf('Unable to load config `%s`: no suitable loader found.', $section));
    }

    private function findFile(string $section, string $extension): ?string
    {
        foreach ($this->directories as $directory) {
            $filename = \sprintf('%s/%s.%s', $directory, $section, $extension);
            if (\file_exists($filename)) {
                return $filename;
            }
        }

        return null;
    }
}
